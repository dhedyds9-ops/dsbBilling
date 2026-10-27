<?php

namespace App\Console\Commands\ISP;

use App\Models\ISP\Olt;
use App\Services\Adapters\Provisioning\Support\SnmpClient;
use App\Services\Adapters\Provisioning\Support\TelnetSshClient;
use Illuminate\Console\Command;

class DebugCdataCommand extends Command
{
    protected $signature = 'olt:debug-cdata {id=1}';
    protected $description = 'Diagnosa mendalam koneksi SNMP & CLI OLT C-Data';

    public function handle(): int
    {
        $id = $this->argument('id');
        $olt = Olt::find($id) ?? Olt::where('ip_address', '192.168.100.2')->first() ?? Olt::first();

        if (!$olt) {
            $this->error("Tidak ditemukan data OLT di database!");
            return 1;
        }

        $this->info("==================================================");
        $this->info("DIAGNOSA OLT CDATA: [ID: {$olt->id}] {$olt->name}");
        $this->info("==================================================");
        $this->line("IP Address         : " . ($olt->ip_address ?? '-'));
        $this->line("SNMP Community     : " . ($olt->snmp_community_read ?: 'public'));
        $this->line("SNMP Port          : " . ($olt->snmp_port ?? 161));
        $this->line("SNMP Version       : " . ($olt->snmp_version ?? '2c'));
        $this->line("Telnet Port        : " . ($olt->cli_port ?? 23));
        $this->line("Telnet User        : " . ($olt->username ?? '-'));
        $this->line("Telnet Password    : " . ($olt->password ? '***TERISI***' : 'KOSONG'));
        $this->newLine();

        $community = $olt->snmp_community_read ?: 'public';
        $port = (int)($olt->snmp_port ?? 161);
        $version = $olt->snmp_version ?? '2c';

        $snmp = new SnmpClient($olt->ip_address, $community, $version, 3000000, 1, $port);

        // 1. SysDescr
        $this->info("[1/5] Menguji Koneksi Dasar SNMP (sysDescr)...");
        $sysDescr = $snmp->get('.1.3.6.1.2.1.1.1.0');
        if ($sysDescr) {
            $this->line("  ✓ sysDescr: {$sysDescr}");
        } else {
            $this->error("  ✗ sysDescr gagal / timeout! Pastikan Community String dan IP benar.");
        }

        // 2. PON Ports
        $this->info("[2/5] Membaca Port PON (.1.3.6.1.4.1.34592.1.3.100.2.1.1.4)...");
        $ports = $snmp->walk('.1.3.6.1.4.1.34592.1.3.100.2.1.1.4');
        $this->line("  Ditemukan " . count($ports) . " port PON.");
        foreach ($ports as $k => $v) {
            $this->line("    Port {$k} => Status: {$v}");
        }

        // 3. Scan ONU Tables
        $this->info("[3/5] Memindai Tabel ONU (SNMP Walk)...");
        $candidates = [
            'FD1608S GPON Config SN (.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.4)' => '.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.4',
            'FD1608S GPON Config Table (.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1)' => '.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1',
            'FD1608S GPON Autofind (.1.3.6.1.4.1.34592.1.5.1.1.2.11.1.1)'     => '.1.3.6.1.4.1.34592.1.5.1.1.2.11.1.1',
            'Standard GPON SN (.1.3.6.1.4.1.34592.1.3.100.9.2.1.3)'             => '.1.3.6.1.4.1.34592.1.3.100.9.2.1.3',
            'Standard GPON Name (.1.3.6.1.4.1.34592.1.3.100.9.2.1.2)'           => '.1.3.6.1.4.1.34592.1.3.100.9.2.1.2',
            'GPON Autofind (.1.3.6.1.4.1.34592.1.3.100.11.1.1.2)'               => '.1.3.6.1.4.1.34592.1.3.100.11.1.1.2',
            'Older GPON SN (.1.3.6.1.4.1.34592.1.3.4.1.1.2)'                   => '.1.3.6.1.4.1.34592.1.3.4.1.1.2',
            'EPON 34592 (.1.3.6.1.4.1.34592.1.3.3.1.1.2)'                       => '.1.3.6.1.4.1.34592.1.3.3.1.1.2',
            'EPON 51810 (.1.3.6.1.4.1.51810.1.3.1.1.1.2)'                      => '.1.3.6.1.4.1.51810.1.3.1.1.1.2',
            'CTC EPON MAC (.1.3.6.1.4.1.17409.2.3.4.1.1.2)'                    => '.1.3.6.1.4.1.17409.2.3.4.1.1.2',
        ];

        $foundAny = false;
        foreach ($candidates as $label => $oid) {
            $res = $snmp->walk($oid);
            if (!empty($res)) {
                $foundAny = true;
                $this->info("  [HASIL DITEMUKAN] {$label}: " . count($res) . " baris");
                foreach (array_slice($res, 0, 5, true) as $k => $v) {
                    $this->line("    {$k} => {$v}");
                }
            } else {
                $this->line("  - {$label}: kosong");
            }
        }

        if (!$foundAny) {
            $this->warn("  Peringatan: Seluruh OID SNMP di atas menghasilkan KOSONG.");
            $this->warn("  Kemungkinan: Belum ada modem yang terdaftar (O5), atau modem masih berada di status Autofind.");
        }

        // 4. Test Telnet CLI
        $this->newLine();
        $this->info("[4/5] Menguji Koneksi Telnet CLI...");
        if (empty($olt->username) || empty($olt->password)) {
            $this->warn("  Username/Password Telnet di database KOSONG. Lewati pengujian Telnet.");
            $this->warn("  Saran: Masukkan Username & Password Telnet di Web billing menu Edit OLT (default biasanya admin/admin).");
        } else {
            try {
                $cli = new TelnetSshClient();
                $cli->connect($olt->ip_address, $olt->username, $olt->password, 'telnet', (int)($olt->cli_port ?? 23));
                $this->info("  ✓ Berhasil login Telnet!");

                $this->line("  Menjalankan perintah 'show ont info all'...");
                $outInfo = $cli->execute("show ont info all\r\n", false);
                $this->line($outInfo ?: '  (Tidak ada output)');

                $this->line("  Menjalankan perintah 'show ont autofind all'...");
                $outAuto = $cli->execute("show ont autofind all\r\n", false);
                $this->line($outAuto ?: '  (Tidak ada output)');
            } catch (\Throwable $e) {
                $this->error("  ✗ Telnet CLI Gagal: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("[5/5] Diagnosa Selesai.");
        return 0;
    }
}
