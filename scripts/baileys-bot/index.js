/* eslint-disable no-console */
'use strict';
/**
 * =============================================================================
 *   dsBilling - Self-Hosted WhatsApp Interactive Bot (Baileys MD)
 *
 *   Feature Summary:
 *   1. QR Code login via terminal (scan dengan WA HP utama).
 *   2. Message Router: incoming text → forward ke backend Laravel dsBilling
 *      POST /api/whatsapp/webhook/baileys/{sigToken}.
 *   3. Outgoing Sender: Kirim text/image/document dari Laravel (jika pakai
 *      BaileysRestWaDriver) — terima webhook dari Laravel di /webhook-outgoing.
 *   4. Anti-banned / Rate Limit:
 *        - sliding window MAX_MSG_PER_MINUTE
 *        - exponential backoff per nomor (SAME_NUMBER_COOLDOWN_S)
 *        - daily cap MAX_DAILY_PER_NUMBER per nomor
 *   5. Auto reconnect + creds persistent (tidak perlu scan ulang setelah restart).
 *   6. Auto-presence "recording" 300ms sebelum reply = tampak natural seperti CS.
 *
 *   Instalasi:
 *     $ cd scripts/baileys-bot
 *     $ copy .env.example .env   (edit sesuai)
 *     $ npm install
 *     $ npm start                 (tampilkan QR code, scan)
 *
 *   Production (PM2):
 *     $ npm run start:pm2
 *     $ pm2 save && pm2 startup
 * =============================================================================
 */
require('dotenv').config();

const path = require('path');
const fs = require('fs');
const crypto = require('crypto');
const {
  default: makeWASocket,
  useMultiFileAuthState,
  DisconnectReason,
  fetchLatestBaileysVersion,
  makeCacheableSignalKeyStore,
  MessageRetryMap,
  getAggregateVotesInPollMessage,
  makeInMemoryStore,
  jidDecode,
  delay,
  getContentType,
} = require('@whiskeysockets/baileys');
const MAIN_LOGGER = require('pino')({ level: process.env.LOG_LEVEL || 'info', transport: { target: 'pino-pretty', options: { colorize: true, singleLine: true } } });
const logger = MAIN_LOGGER.child({});
logger.level = process.env.LOG_LEVEL || 'info';
const axios = require('axios');

// ----------------------------------------------------------------------------
// ENV
// ----------------------------------------------------------------------------
const env = (k, d) => process.env[k] ?? d;
const DSBILLING_BASE_URL = String(env('DSBILLING_BASE_URL', 'http://localhost:8000')).replace(/\/+$/, '');
const DSBILLING_WEBHOOK_SIGTOKEN = String(env('DSBILLING_WEBHOOK_SIGTOKEN', ''));
const SESSION_NAME = String(env('SESSION_NAME', 'dsbilling-primary'));
const SESSION_FOLDER = path.resolve(__dirname, String(env('SESSION_FOLDER', './auth_sessions')));
const MAX_MSG_PER_MINUTE = Number(env('MAX_MSG_PER_MINUTE', 60));
const BROADCAST_DELAY_MS = Number(env('BROADCAST_DELAY_MS', 1200));
const SAME_NUMBER_COOLDOWN_S = Number(env('SAME_NUMBER_COOLDOWN_S', 90));
const MAX_DAILY_PER_NUMBER = Number(env('MAX_DAILY_PER_NUMBER', 20));

if (!DSBILLING_WEBHOOK_SIGTOKEN || DSBILLING_WEBHOOK_SIGTOKEN.length < 16) {
  console.error('[CONFIG][FATAL] DSBILLING_WEBHOOK_SIGTOKEN di .env minimal 16 karakter. Isi dengan: substr(md5(APP_KEY_LARAVEL+\':baileys\'),0,24)');
  process.exit(1);
}

const LOG_DIR = path.resolve(__dirname, String(env('LOG_DIR', './logs')));
if (!fs.existsSync(LOG_DIR)) fs.mkdirSync(LOG_DIR, { recursive: true });
if (!fs.existsSync(SESSION_FOLDER)) fs.mkdirSync(SESSION_FOLDER, { recursive: true });

// ----------------------------------------------------------------------------
// IN-MEMORY STORE untuk message history + rate limit counter.
// Untuk prod >1000 pesan/hari → ganti ke SQLite/Redis.
// ----------------------------------------------------------------------------
const store = makeInMemoryStore({ logger });
store.readFromFile(path.join(SESSION_FOLDER, `store-${SESSION_NAME}.json`));
setInterval(() => { store.writeToFile(path.join(SESSION_FOLDER, `store-${SESSION_NAME}.json`)); }, 30000);

/** Sliding window counter key=prefix  value=[timestamp,...] */
class SlidingWindow {
  constructor(windowMs = 60000, maxEvents = 60) { this.wMs = windowMs; this.max = maxEvents; this.bucket = []; }
  acquire(n = 1) {
    const now = Date.now();
    this.bucket = this.bucket.filter(t => t > now - this.wMs);
    if (this.bucket.length + n <= this.max) {
      for (let i = 0; i < n; i++) this.bucket.push(now);
      return true;
    }
    return false;
  }
  count() {
    this.bucket = this.bucket.filter(t => t > Date.now() - this.wMs);
    return this.bucket.length;
  }
}
const globalPerMinute = new SlidingWindow(60000, MAX_MSG_PER_MINUTE);
const sentByNumber = new Map(); // phone -> {lastSentTs, todayCount, todayDate}

// Helper: normalisasi nomor Indonesia → 628xxxxx (tanpa +, tanpa spasi)
function normalizePhone(raw) {
  if (!raw) return '';
  let s = String(raw).replace(/[^0-9]/g, '');
  if (s.startsWith('62')) s = s;
  else if (s.startsWith('0')) s = '62' + s.slice(1);
  else if (s.startsWith('8')) s = '62' + s;
  else return s;
  return /^628[0-9]{8,14}$/.test(s) ? s : '';
}

/** cek nomor: boleh kirim? Jika tidak return alasan string */
function canSendTo(phoneNorm, priorityHigh = false) {
  if (!phoneNorm) return { ok: false, reason: 'invalid_phone' };
  if (priorityHigh) return { ok: true };
  const now = Date.now();
  const per = sentByNumber.get(phoneNorm) ?? { lastSentTs: 0, todayCount: 0, todayDate: '' };
  const today = new Date().toISOString().slice(0, 10);
  if (per.todayDate !== today) { per.todayDate = today; per.todayCount = 0; }
  if (per.todayCount >= MAX_DAILY_PER_NUMBER) return { ok: false, reason: 'daily_cap' };
  if (per.lastSentTs + SAME_NUMBER_COOLDOWN_S * 1000 > now) return { ok: false, reason: 'cooldown' };
  if (!globalPerMinute.acquire(1)) return { ok: false, reason: 'throttled_minute' };
  return { ok: true, per };
}

function markSent(phoneNorm) {
  const n = sentByNumber.get(phoneNorm) ?? { lastSentTs: 0, todayCount: 0, todayDate: new Date().toISOString().slice(0, 10) };
  n.lastSentTs = Date.now();
  if (n.todayDate !== new Date().toISOString().slice(0, 10)) { n.todayDate = new Date().toISOString().slice(0, 10); n.todayCount = 0; }
  n.todayCount += 1;
  sentByNumber.set(phoneNorm, n);
}

// ----------------------------------------------------------------------------
// FORWARD incoming ke backend dsBilling
// ----------------------------------------------------------------------------
async function forwardToBackend(jid, msgText, pushName, msgContext) {
  const phone = normalizePhone(decodeJid(jid));
  if (!phone) return;
  try {
    const body = {
      event: 'messages.upsert',
      timestamp: Math.floor(Date.now() / 1000),
      from_jid: jid,
      from_phone: phone,
      sender_name: pushName || '',
      message_id: msgContext?.msgId || '',
      message_type: 'text',
      text: msgText,
      raw: JSON.stringify(msgContext?.raw || {}),
    };
    const url = `${DSBILLING_BASE_URL}/api/whatsapp/webhook/baileys/${DSBILLING_WEBHOOK_SIGTOKEN}`;
    const hdr = { 'Content-Type': 'application/json', 'x-wa-bot-session': SESSION_NAME };
    logger.debug({ phone, text_len: msgText.length }, '[dsBilling] forward incoming');
    await axios.post(url, body, { headers: hdr, timeout: 15000, validateStatus: () => true });
  } catch (e) {
    logger.warn({ err: String(e && e.message || e) }, '[dsBilling] forward gagal');
  }
}

// ----------------------------------------------------------------------------
// BOT REPLY handler (optional: jika backend terlalu lambat / fallback lokal)
//   Catatan: reply SEBENARNYA (tagihan/status/bayar/gangguan) dihandle LARAVEL
//   -> balasan dikirim balik lewat sendWa dari dsBilling via BaileysRestWaDriver
//   Fungsi ini cuma greeting + help bila backend OFFLINE selama 15detik.
// ----------------------------------------------------------------------------
const HELP_TEXT = `🤖 Bot dsBilling (lokal fallback):
Perintah tersedia:
• tagihan  → Daftar tagihan aktif
• status   → Status koneksi & paket
• bayar    → Link pembayaran cepat
• gangguan → Buat tiket laporan
• bantuan  → Menu ini`;

function isLikelyCommand(text) {
  const t = text.toLowerCase().trim();
  return ['tagihan', 'bill', 'invoice', 'list', 'status', 'cekinet', 'ping', 'bayar', 'pay', 'payment', 'gangguan', 'tiket', 'trouble', 'lapor', 'bantuan', 'help', 'menu', 'halo', 'hallo', 'hi', 'test', 'p'].includes(t);
}

function decodeJid(jid) {
  if (!jid) return '';
  try {
    const d = jidDecode(jid);
    if (d) return d.user;
  } catch (_) { /* noop */ }
  return String(jid).split('@')[0] || '';
}

// ----------------------------------------------------------------------------
// START Baileys Socket
// ----------------------------------------------------------------------------
async function start() {
  const { state, saveCreds } = await useMultiFileAuthState(path.join(SESSION_FOLDER, `baileys-session-${SESSION_NAME}`));
  const { version, isLatest } = await fetchLatestBaileysVersion().catch(() => ({ version: [2, 3000, 1015901439], isLatest: false }));
  logger.info({ version, isLatest }, `[WA] Starting Baileys MD session=${SESSION_NAME}`);

  const sock = makeWASocket({
    version,
    printQRInTerminal: true,
    auth: { creds: state.creds, keys: makeCacheableSignalKeyStore(state.keys, logger) },
    logger,
    generateHighQualityLinkPreview: false,
    syncFullHistory: false,
    patchMessageBeforeSending: (msg) => msg,
    getMessage: async (key) => {
      const msg2 = await store.loadMessage(key.remoteJid, key.id);
      return msg2?.message || undefined;
    },
    shouldSyncHistoryMessage: (msg) => true,
  });
  store.bind(sock.ev);

  sock.ev.on('creds.update', saveCreds);

  // ---------------- CONNECTION MANAGER (auto reconnect + QR) ----------------
  sock.ev.on('connection.update', (update) => {
    const { connection, lastDisconnect, qr } = update || {};
    if (qr) {
      console.log('\n┌─────────────────────────────────────────────────────┐');
      console.log('│      SCAN QR INI DENGAN HP WHATSAPP UTAMA ANDA       │');
      console.log('│  Setting → Perangkat Tertaut → Tautkan Perangkat     │');
      console.log('└─────────────────────────────────────────────────────┘');
    }
    if (connection === 'close') {
      const statusCode = lastDisconnect?.error?.output?.statusCode;
      const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
      logger.warn({ statusCode, reason: lastDisconnect?.error?.message }, '[WA] Disconnected');
      if (shouldReconnect) {
        logger.info('[WA] Auto reconnect in 5s...');
        setTimeout(start, 5000);
      } else {
        logger.error('[WA] LOGGED OUT. Hapus folder auth_sessions & scan ulang.');
        process.exit(0);
      }
    } else if (connection === 'open') {
      logger.info(`[WA] TERHUBUNG ✅  session=${SESSION_NAME}`);
      logger.info(`[WA] dsBilling endpoint: ${DSBILLING_BASE_URL}/api/whatsapp/webhook/baileys/${'*'.repeat(8)}${DSBILLING_WEBHOOK_SIGTOKEN.slice(-4)}`);
    }
  });

  // ---------------- INCOMING MESSAGES ----------------
  sock.ev.on('messages.upsert', async (m) => {
    if (m.type !== 'notify') return;
    for (const msg of m.messages) {
      try {
        if (msg.key?.fromMe) continue; // abaikan pesan dari akun bot sendiri
        const jid = msg.key?.remoteJid;
        if (!jid || String(jid).endsWith('@g.us')) continue; // untuk saat ini: ABANDON GROUP CHAT (fokus private 1:1)
        const type = getContentType(msg.message);
        if (type !== 'conversation' && type !== 'extendedTextMessage') continue;
        let text = '';
        if (type === 'conversation') text = String(msg.message?.conversation || '');
        else if (type === 'extendedTextMessage') text = String(msg.message?.extendedTextMessage?.text || '');
        if (!text.trim()) continue;

        const pushName = String(msg.pushName || '');
        logger.info({ jid, preview: text.slice(0, 80), pushName }, '[IN]');

        // Kirim presence "sedang mengetik" supaya natural
        try { await sock.sendPresenceUpdate('composing', jid); } catch (_) {}
        await delay(300);

        // 1) Forward ke dsBilling Laravel (SSOT pemrosesan perintah)
        forwardToBackend(jid, text, pushName, { msgId: msg.key?.id, raw: msg }).catch(() => {});

        // 2) Fallback LOKAL: jika command sederhana + backend unreachable, reply bantuan minimal
        //    (TIDAK perlu untuk prod; backup ketika Laravel maintenance)
        if (isLikelyCommand(text)) {
          try {
            await axios.get(`${DSBILLING_BASE_URL}/up`, { timeout: 3000 }).catch(() => { throw new Error('offline'); });
          } catch (_backendOffline) {
            const reply = /bantuan|help|menu|\?$/i.test(text) ? HELP_TEXT : 'ℹ️ Permintaan diterima. Sistem billing sedang maintenance, mohon coba beberapa saat lagi atau hubungi CS.';
            try {
              const r = canSendTo(normalizePhone(decodeJid(jid)), true);
              if (r.ok) {
                await sock.sendMessage(jid, { text: reply });
                markSent(normalizePhone(decodeJid(jid)));
              }
            } catch (e) { logger.warn({ err: String(e.message || e) }, '[LOCAL-FALLBACK] reply fail'); }
          }
        }
        try { await sock.sendPresenceUpdate('paused', jid); } catch (_) {}
      } catch (err) {
        logger.warn({ err: String(err.message || err) }, '[IN] process error');
      }
    }
  });

  // ---------------- GROUP / BROADCAST SENDER: listen HTTP untuk trigger dari Laravel ----------------
  // Opsional: Bila Ingress gak mau expose 3000, disable fitur ini & gunakan BaileysRestWaDriver saja
  // (BaileysRestWaDriver menembak endpoint Baileys REST server) — ini hanya fallback sederhana.
  const http = require('http');
  const PORT = Number(process.env.HTTP_PORT || 3001);
  const server = http.createServer(async (req, res) => {
    let body = '';
    req.on('data', (c) => { body += c; });
    req.on('end', async () => {
      if (req.method !== 'POST' || !req.url.startsWith('/send-wa')) {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        return res.end(JSON.stringify({ ok: true, service: 'dsbilling-baileys-bot', session: SESSION_NAME }));
      }
      const tok = req.headers['x-out-token'];
      if (!tok || !crypto.timingSafeEqual(Buffer.from(String(tok)), Buffer.from(DSBILLING_WEBHOOK_SIGTOKEN))) {
        res.writeHead(403);
        return res.end(JSON.stringify({ ok: false, error: 'invalid_out_token' }));
      }
      let payload;
      try { payload = JSON.parse(body || '{}'); } catch (_) {
        res.writeHead(400); return res.end(JSON.stringify({ ok: false, error: 'bad_json' }));
      }
      const phone = normalizePhone(payload.to_phone || payload.to || '');
      const text = String(payload.text || '');
      const priorityHigh = !!payload.priority_high;
      if (!phone || !text) {
        res.writeHead(422); return res.end(JSON.stringify({ ok: false, error: 'invalid_payload' }));
      }
      const jid = phone + '@s.whatsapp.net';
      const check = canSendTo(phone, priorityHigh);
      if (!check.ok) {
        res.writeHead(429); return res.end(JSON.stringify({ ok: false, error: check.reason, next_ready_in_ms: SAME_NUMBER_COOLDOWN_S * 1000 }));
      }
      try {
        await delay(BROADCAST_DELAY_MS);
        await sock.sendMessage(jid, { text });
        markSent(phone);
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ ok: true, jid, sent_at: new Date().toISOString() }));
      } catch (e) {
        res.writeHead(502);
        res.end(JSON.stringify({ ok: false, error: String(e.message || e) }));
      }
    });
  });
  server.listen(PORT, () => logger.info(`[HTTP] Outgoing sender listen on http://127.0.0.1:${PORT}/send-wa`));

  // Graceful shutdown
  ['SIGTERM', 'SIGINT'].forEach(sig => process.on(sig, () => {
    logger.info({ sig }, '[SHUTDOWN] bye');
    try { store.writeToFile(path.join(SESSION_FOLDER, `store-${SESSION_NAME}.json`)); } catch (_) {}
    process.exit(0);
  }));
}

start().catch(err => {
  console.error('[FATAL] start() gagal:', err);
  process.exit(1);
});
