/**
 * dsBilling Enterprise - GenieACS Event Ingestion Extension
 * Compatible with GenieACS v1.2.16+
 *
 * Location: /opt/genieacs/config/ext/dsbilling-webhook.js (or configured EXT_DIR)
 *
 * Security:
 * - HMAC-SHA256 signature
 * - Deterministic Event ID
 * - Request timeout handling
 * - Zero sensitive credentials in payload
 */

"use strict";

const http = require("http");
const https = require("https");
const crypto = require("crypto");
const url = require("url");

const WEBHOOK_URL = process.env.DSBILLING_WEBHOOK_URL || "https://winets.mstore.id/api/webhooks/genieacs/inform";
const WEBHOOK_SECRET = process.env.GENIEACS_WEBHOOK_SECRET || process.env.DSBILLING_WEBHOOK_SECRET || "dsbilling-genieacs-secret-key";
const TIMEOUT_MS = parseInt(process.env.DSBILLING_WEBHOOK_TIMEOUT || "5000", 10);

/**
 * Inform handler called by GenieACS provision script:
 * ext("dsbilling-webhook", "inform", payloadJsonString)
 */
function inform(args, callback) {
    if (typeof callback !== "function") {
        callback = function () {};
    }

    try {
        let rawInput = args;
        if (Array.isArray(args) && args.length > 0) {
            rawInput = args[0];
        }

        let data = {};
        if (typeof rawInput === "string") {
            try {
                data = JSON.parse(rawInput);
            } catch (parseErr) {
                data = { raw: rawInput };
            }
        } else if (typeof rawInput === "object" && rawInput !== null) {
            data = rawInput;
        }

        const deviceId = data.device_id || data.device || "";
        const serialNumber = data.serial_number || "";
        const lastInform = data.last_inform || new Date().toISOString();
        const eventCodes = Array.isArray(data.event_codes) ? data.event_codes : [];

        // Generate deterministic event ID
        const eventIdSource = `${deviceId}|${lastInform}|${eventCodes.join(",")}`;
        const eventId = data.event_id || crypto.createHash("sha256").update(eventIdSource).digest("hex");

        const receivedAt = new Date().toISOString();

        const payload = {
            event_id: eventId,
            source: "genieacs",
            event_type: "inform",
            event_codes: eventCodes,
            device_id: deviceId,
            serial_number: serialNumber,
            manufacturer: data.manufacturer || null,
            product_class: data.product_class || null,
            software_version: data.software_version || null,
            ip_address: data.ip_address || null,
            last_inform: lastInform,
            received_at: receivedAt
        };

        const jsonBody = JSON.stringify(payload);
        const signature = crypto.createHmac("sha256", WEBHOOK_SECRET).update(jsonBody).digest("hex");

        const parsedUrl = url.parse(WEBHOOK_URL);
        const isHttps = parsedUrl.protocol === "https:";
        const client = isHttps ? https : http;

        const options = {
            hostname: parsedUrl.hostname,
            port: parsedUrl.port || (isHttps ? 443 : 80),
            path: parsedUrl.path || "/api/webhooks/genieacs/inform",
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Content-Length": Buffer.byteLength(jsonBody),
                "X-GenieACS-Signature": `sha256=${signature}`,
                "X-GenieACS-Timestamp": receivedAt,
                "User-Agent": "GenieACS-EventBridge/1.2.16"
            },
            timeout: TIMEOUT_MS
        };

        const req = client.request(options, function (res) {
            let responseBody = "";
            res.setEncoding("utf8");
            res.on("data", function (chunk) {
                responseBody += chunk;
            });
            res.on("end", function () {
                callback(null, {
                    status: res.statusCode >= 200 && res.statusCode < 300 ? "success" : "http_error",
                    statusCode: res.statusCode,
                    response: responseBody
                });
            });
        });

        req.on("error", function (err) {
            // Fail safely: do not crash GenieACS worker process
            callback(null, {
                status: "network_error",
                error: err.message
            });
        });

        req.on("timeout", function () {
            req.destroy();
            callback(null, {
                status: "timeout",
                error: `Request timed out after ${TIMEOUT_MS}ms`
            });
        });

        req.write(jsonBody);
        req.end();

    } catch (err) {
        callback(null, {
            status: "exception",
            error: err.message
        });
    }
}

exports.inform = inform;
