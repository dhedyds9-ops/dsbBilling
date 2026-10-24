<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ISP\VoucherTemplateCategory;
use App\Models\ISP\VoucherTemplate;
use App\Models\ISP\VoucherTemplateVersion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VoucherTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedConnection(config('database.default'));

        // If tenant database exists (e.g. winets), seed it as well
        $tenantSqlite = database_path('tenants/winets.sqlite');
        if (file_exists($tenantSqlite)) {
            $this->seedTenantSqlite($tenantSqlite);
        }
    }

    private function getTemplatesData(): array
    {
        return [
            [
                'name' => 'Voucher MSRadius Dynamic Color',
                'slug' => 'voucher-msradius-color',
                'category' => VoucherTemplateCategory::Classic->value,
                'description' => 'Template voucher dinamis bergaya MSRadius / Mikhmon dengan warna otomatis sesuai nominal harga (Rp 2rb - 70rb), logo, dan QR Code login.',
                'is_system' => false,
                'is_active' => true,
                'is_default' => true,
                'settings' => [
                    'paper_size' => 'A4',
                    'grid_preset' => '3x6',
                    'page_margins' => ['top' => 5, 'right' => 5, 'bottom' => 5, 'left' => 5],
                    'card_margins' => ['top' => 2, 'right' => 2, 'bottom' => 2, 'left' => 2],
                ],
                'css_code' => '.qrcode img {
    height: 50px !important;
    width: 50px !important;
    border-radius: 4px;
    box-sizing: border-box;
    display: inline-block;
}',
                'template_code' => '{assign var="v_price" value=$voucher.price}
{assign var="pkg" value=$package.name}
{if $v_price == 2000 || $v_price <= 2000 || $pkg == "1 JAM" || $pkg == "1 Jam" || $pkg == "2 JAM" || $pkg == "2 Jam"}
 {assign var="color" value="#000000"}
{elseif $v_price == 3000 || $pkg == "3 JAM" || $pkg == "3 Jam" || $pkg == "6 JAM" || $pkg == "6 Jam"}
 {assign var="color" value="#0ca5ff"}
{elseif $v_price == 5000 || $pkg == "12 JAM" || $pkg == "12 Jam"}
 {assign var="color" value="#FF0000"}
{elseif $v_price == 7000 || $pkg == "24 JAM" || $pkg == "24 Jam" || $pkg == "1 Hari"}
 {assign var="color" value="#A52A2A"}
{elseif $v_price == 10000}
 {assign var="color" value="#10b981"}
{elseif $v_price == 20000 || $v_price == 25000}
 {assign var="color" value="#8B008B"}
{elseif $v_price == 50000 || $v_price == 65000}
 {assign var="color" value="#FF00FF"}
{elseif $v_price >= 70000}
 {assign var="color" value="#32CD32"}
{else}
 {assign var="color" value="#0ca5ff"}
{/if}
<table style="display:inline-block; border-collapse:collapse; border:1.5px solid #444; margin:1.5px; width:210px; position:relative; padding:0px; margin-right:8px; margin-bottom:10px; background:#fff; font-family:Arial,sans-serif; vertical-align:top; box-sizing:border-box;">
 <tbody>
 <tr>
 <td style="color:#666; padding:2px 4px;" valign="top">
 <table style="width:100%; border-collapse:collapse;">
 <tbody>
 <tr> 
 <td style="width:85px; vertical-align:middle;">
 {if $company.logo}
 <img style="object-fit:contain; max-height:22px; max-width:85px; display:inline-block; vertical-align:middle;" src="{{company.logo}}" alt="logo" onerror="this.style.display=\'none\'">
 {else}
 <span style="font-weight:bold; font-size:12px; color:{$color};">{if $company.name}{{company.name}}{else}WINETS.ID{/if}</span>
 {/if}
 </td> 
 <td style="text-align:right; vertical-align:middle;">
 <div style="font-weight:bold; font-size:18px; color:{$color}; line-height:1.1;"> 
 <small style="font-size:12px;">Rp</small>
 {{voucher.price|number_format(0, \',\', \'.\')}}
 </div> 
 </td> 
 </tr>
 </tbody>
 </table>
 </td>
 </tr>
 <tr>
 <td style="color:#666; border-collapse:collapse; padding:2px 4px;" valign="top">
 <table style="width:100%; border-collapse:collapse;">
 <tbody>
 <tr>
 <td style="width:125px; vertical-align:top;">
 <div style="clear:both; color:#555; margin-bottom:2px;">
 {if $voucher.password == $voucher.code || empty($voucher.password)} 
 <div style="border-bottom:1px solid #ccc; text-align:center; font-weight:bold; font-size:10px; color:#444;">KODE VOUCHER</div> 
 <div style="padding:2px; border-bottom:1px solid #ccc; text-align:center; font-weight:bold; font-size:14px; color:#000; font-family:monospace;">{{voucher.code}}</div>
 {else}
 <div style="border-bottom:1px solid #ccc; text-align:center; font-weight:bold; font-size:9px; color:#444;">MEMBER</div> 
 <div style="padding:1px; border-bottom:1px solid #ccc; text-align:center; font-weight:bold; font-size:11px; color:{$color}; font-family:monospace; line-height:1.2;">
 {{voucher.code}}<br>{{voucher.password}}
 </div>
 {/if}
 </div>
 <div style="text-align:left; color:#111; font-size:8px; font-weight:bold; line-height:1.25; margin-top:2px;">
 Durasi : {if $package.duration}{{package.duration}}{elseif $package.name}{{package.name}}{else}3 Jam{/if}<br>
 Masa Aktif : {if $voucher.validity}{{voucher.validity}}{else}3 jam{/if}<br>
 Tgl Cetak : {{voucher.created_at}}<br>
 Agen : {if $owner.name}{{owner.name}}{else}Owner Demo{/if}<br>
 </div>
 </td> 
 <td style="width:75px; text-align:right; vertical-align:middle;">
 <div class="qrcode" style="display:inline-block;">
   <img style="border:1.5px solid {$color}; width:50px; height:50px; display:inline-block;" src="{qr($voucher.code, 100)}" alt="QR" />
 </div>
 </td> 
 </tr>
 <tr>
 <td style="background:{$color}; color:#fff; padding:2px 4px;" valign="top" colspan="2">
 <div style="text-align:left; color:#fff; font-size:8px; font-weight:bold; padding:1px 2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
 Login manual: {if $voucher.login_url}{{voucher.login_url}}{else}http://wifi.demo.id/login{/if}
 </div>
 </td> 
 </tr>
 </tbody>
 </table>
 </td>
 </tr>
 </tbody>
</table>',
            ],
            [
                'name' => 'MSRadius Template 107 (Dynamic Color)',
                'slug' => 'msradius-template-107-dynamic',
                'category' => VoucherTemplateCategory::Classic->value,
                'description' => 'Template voucher MSRadius 107 dengan sudut badge harga melengkung, kotak kode voucher tebal, kuota & durasi, instruksi WiFi, serta badge WA dan domain login dengan aksen warna dinamis otomatis sesuai nominal harga.',
                'is_system' => false,
                'is_active' => true,
                'is_default' => false,
                'settings' => [
                    'paper_size' => 'A4',
                    'grid_preset' => '3x6',
                    'page_margins' => ['top' => 5, 'right' => 5, 'bottom' => 5, 'left' => 5],
                    'card_margins' => ['top' => 2, 'right' => 2, 'bottom' => 2, 'left' => 2],
                ],
                'css_code' => '',
                'template_code' => '{assign var="v_price" value=$voucher.price}
{assign var="pkg" value=$package.name}
{if $v_price <= 2000 || $pkg == "1 JAM" || $pkg == "1 Jam" || $pkg == "2 JAM" || $pkg == "2 Jam"}
 {assign var="color" value="#1e293b"}
{elseif $v_price == 3000 || $pkg == "3 JAM" || $pkg == "3 Jam" || $pkg == "6 JAM" || $pkg == "6 Jam"}
 {assign var="color" value="#00a2ff"}
{elseif $v_price == 5000 || $pkg == "12 JAM" || $pkg == "12 Jam"}
 {assign var="color" value="#e11d48"}
{elseif $v_price == 7000 || $pkg == "24 JAM" || $pkg == "24 Jam" || $pkg == "1 Hari"}
 {assign var="color" value="#8b5cf6"}
{elseif $v_price == 10000}
 {assign var="color" value="#10b981"}
{elseif $v_price == 20000 || $v_price == 25000}
 {assign var="color" value="#f59e0b"}
{elseif $v_price >= 50000}
 {assign var="color" value="#ec4899"}
{else}
 {assign var="color" value="#00a2ff"}
{/if}
<div style="display:inline-block; width:220px; border:1.5px solid {$color}; border-radius:6px; background:#fff; overflow:hidden; margin:3px; vertical-align:top; font-family:Arial,Helvetica,sans-serif; box-sizing:border-box;">
  <table style="width:100%; border-collapse:collapse;">
    <tr>
      <td style="padding:3px 0 0 5px; vertical-align:middle;">
        {if $company.logo}
        <img src="{{company.logo}}" alt="Logo" style="max-height:18px; max-width:70px; object-fit:contain; vertical-align:middle;" onerror="this.style.display=\'none\'">
        {/if}
        <span style="font-size:11px; font-weight:800; color:#0284c7;">{if $company.name}{{company.name}}{else}WINETS.ID{/if}</span>
      </td>
      <td style="text-align:right; vertical-align:top; width:95px;">
        <div style="background:{$color}; color:#fff; font-size:12px; font-weight:900; padding:2px 8px; border-bottom-left-radius:10px; display:inline-block; float:right; line-height:1.2;">
          Rp. {{voucher.price|number_format(0, \',\', \'.\')}}
        </div>
      </td>
    </tr>
  </table>
  <div style="font-size:8.5px; font-weight:800; color:#1e293b; padding-left:6px; margin-top:2px;">KODE VOUCHER</div>
  <table style="width:100%; border-collapse:collapse; padding:0 6px;">
    <tr>
      <td style="width:58%; padding:2px 4px 2px 6px;">
        <div style="border:2px solid {$color}; color:{$color}; font-size:14px; font-weight:900; font-family:monospace; text-align:center; padding:3px 2px; border-radius:4px; line-height:1;">
          {{voucher.code}}
        </div>
      </td>
      <td style="width:42%; text-align:center; padding:2px 6px 2px 2px; line-height:1.15;">
        <div style="font-size:13px; font-weight:900; color:#0f172a;">{if $package.duration}{{package.duration}}{elseif $package.name}{{package.name}}{else}6 JAM{/if}</div>
        <div style="font-size:7.5px; color:#64748b; font-weight:600;">Masa Pakai</div>
        <div style="font-size:8px; color:#0f172a; font-weight:700;">Kuota {if $package.quota}{{package.quota}}{else}Unlimited{/if}</div>
      </td>
    </tr>
  </table>
  <div style="text-align:center; font-size:7.5px; font-weight:700; color:#475569; line-height:1.15; padding:2px 4px;">
    Sambungkan Ke WiFi {if $company.name}{{company.name}}{else}Winets.id{/if}<br>
    <span style="font-size:7px; font-weight:normal; color:#64748b;">jika tidak muncul otomatis buka google</span>
  </div>
  <table style="width:100%; border-collapse:collapse; background:#f8fafc; border-top:1px solid #e2e8f0; padding:2px 4px;">
    <tr>
      <td style="padding:2px 4px; text-align:left;">
        <span style="background:#10b981; color:#fff; font-size:7.5px; font-weight:800; padding:1px 5px; border-radius:8px;">
          &#9742; {if $company.phone}{{company.phone}}{else}083139935920{/if}
        </span>
      </td>
      <td style="padding:2px 4px; text-align:right; font-size:7.5px; font-weight:800; color:#0f172a;">
        &#127760; <span style="font-family:monospace;">{if $voucher.login_url}{{voucher.login_url}}{else}http://winets.id{/if}</span>
      </td>
    </tr>
  </table>
</div>',
            ],
            [
                'name' => 'Kartu Modern WiFi (A4 Grid)',
                'slug' => 'kartu-modern-wifi-a4',
                'category' => VoucherTemplateCategory::Modern->value,
                'description' => 'Template kartu voucher modern untuk dicetak di kertas A4 (grid 3x6). Dilengkapi logo, QR Code login, username & password jelas, badge harga dan durasi.',
                'is_system' => true,
                'is_active' => true,
                'is_default' => false,
                'settings' => [
                    'paper_size' => 'A4',
                    'grid_preset' => '3x6',
                    'page_margins' => ['top' => 5, 'right' => 5, 'bottom' => 5, 'left' => 5],
                    'card_margins' => ['top' => 2, 'right' => 2, 'bottom' => 2, 'left' => 2],
                ],
                'css_code' => '.card-modern {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    padding: 8px 10px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #1e293b;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
}
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1.5px dashed #e2e8f0;
    padding-bottom: 5px;
    margin-bottom: 6px;
}
.brand-name {
    font-weight: 800;
    font-size: 13px;
    color: #4f46e5;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.pkg-badge {
    background: #eef2ff;
    color: #4338ca;
    padding: 2px 7px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 700;
    border: 1px solid #c7d2fe;
}
.card-body {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}
.cred-box {
    flex: 1;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 4px 6px;
}
.cred-row {
    display: flex;
    justify-content: space-between;
    font-size: 10px;
    margin-bottom: 3px;
}
.cred-row:last-child {
    margin-bottom: 0;
}
.cred-label {
    color: #64748b;
    font-weight: 600;
}
.cred-val {
    font-weight: 800;
    font-family: "Courier New", Courier, monospace;
    font-size: 12px;
    color: #0f172a;
    letter-spacing: 0.5px;
}
.qr-box {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 2px;
}
.qr-box img {
    width: 100%;
    height: 100%;
    display: block;
}
.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 9px;
    color: #64748b;
    border-top: 1px solid #f1f5f9;
    padding-top: 4px;
}
.price-tag {
    font-weight: 800;
    font-size: 12px;
    color: #059669;
}
.meta-info {
    font-size: 9px;
    color: #475569;
    font-weight: 600;
}',
                'template_code' => '<div class="card-modern">
    <div class="card-header">
        <span class="brand-name">{{company.name}}</span>
        <span class="pkg-badge">{{package.name}}</span>
    </div>
    <div class="card-body">
        <div class="cred-box">
            <div class="cred-row">
                <span class="cred-label">User:</span>
                <span class="cred-val">{{voucher.username}}</span>
            </div>
            <div class="cred-row">
                <span class="cred-label">Pass:</span>
                <span class="cred-val">{{voucher.password}}</span>
            </div>
        </div>
        <div class="qr-box">
            <img src="{qr($voucher.code, 100)}" alt="QR" />
        </div>
    </div>
    <div class="card-footer">
        <span class="price-tag">{{package.price|currency}}</span>
        <span class="meta-info">Durasi: {{package.duration}}</span>
    </div>
</div>',
            ],
            [
                'name' => 'Struk Thermal Kasir (58mm/80mm)',
                'slug' => 'struk-thermal-kasir',
                'category' => VoucherTemplateCategory::Thermal->value,
                'description' => 'Template struk vertikal ringkas untuk printer kasir thermal POS (58mm atau 80mm). Praktis untuk kasir/loket pembayaran.',
                'is_system' => true,
                'is_active' => true,
                'is_default' => false,
                'settings' => [
                    'paper_size' => 'THERMAL_58MM',
                    'grid_preset' => null,
                    'page_margins' => ['top' => 2, 'right' => 2, 'bottom' => 2, 'left' => 2],
                    'card_margins' => ['top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1],
                ],
                'css_code' => '.thermal-receipt {
    width: 100%;
    max-width: 200px;
    margin: 0 auto;
    font-family: "Courier New", Courier, monospace;
    font-size: 11px;
    color: #000000;
    line-height: 1.3;
    text-align: center;
    padding: 6px 4px;
    box-sizing: border-box;
}
.thermal-header {
    margin-bottom: 6px;
    border-bottom: 1px dashed #000;
    padding-bottom: 4px;
}
.thermal-title {
    font-size: 14px;
    font-weight: bold;
    text-transform: uppercase;
}
.thermal-sub {
    font-size: 10px;
}
.thermal-divider {
    border-top: 1px dashed #000;
    margin: 5px 0;
}
.thermal-pkg {
    font-size: 12px;
    font-weight: bold;
    margin: 4px 0;
}
.thermal-cred {
    background: #fff;
    border: 1px solid #000;
    padding: 6px 4px;
    margin: 6px 0;
    text-align: left;
}
.thermal-row {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    margin-bottom: 2px;
}
.thermal-val {
    font-weight: bold;
    font-size: 13px;
}
.thermal-qr {
    margin: 6px auto;
    width: 80px;
    height: 80px;
}
.thermal-qr img {
    width: 100%;
    height: 100%;
    display: block;
}
.thermal-footer {
    font-size: 9px;
    margin-top: 6px;
    border-top: 1px dashed #000;
    padding-top: 4px;
}',
                'template_code' => '<div class="thermal-receipt">
    <div class="thermal-header">
        <div class="thermal-title">{{company.name}}</div>
        <div class="thermal-sub">HOTSPOT INTERNET VOUCHER</div>
    </div>
    <div class="thermal-pkg">{{package.name}}</div>
    <div class="thermal-divider"></div>
    <div class="thermal-cred">
        <div class="thermal-row">
            <span>Username:</span>
            <span class="thermal-val">{{voucher.username}}</span>
        </div>
        <div class="thermal-row">
            <span>Password:</span>
            <span class="thermal-val">{{voucher.password}}</span>
        </div>
    </div>
    <div class="thermal-qr">
        <img src="{qr($voucher.code, 120)}" alt="Scan Login" />
    </div>
    <div style="font-size: 10px; margin: 3px 0;">Scan QR untuk Login Otomatis</div>
    <div class="thermal-divider"></div>
    <div style="display:flex; justify-content:space-between; font-weight:bold; font-size:11px;">
        <span>Harga:</span>
        <span>{{package.price|currency}}</span>
    </div>
    <div style="display:flex; justify-content:space-between; font-size:10px;">
        <span>Masa Aktif:</span>
        <span>{{package.duration}}</span>
    </div>
    <div class="thermal-footer">
        <div>Terima kasih atas kunjungan Anda</div>
        <div>Simpan struk ini selama pemakaian</div>
    </div>
</div>',
            ],
            [
                'name' => 'Kartu Minimalis Hemat Tinta (A4)',
                'slug' => 'kartu-minimalis-hemat-tinta',
                'category' => VoucherTemplateCategory::InkSaver->value,
                'description' => 'Template kartu hitam-putih tanpa background tebal untuk menghemat tinta printer biasa (Inkjet/Laser).',
                'is_system' => true,
                'is_active' => true,
                'is_default' => false,
                'settings' => [
                    'paper_size' => 'A4',
                    'grid_preset' => '3x6',
                    'page_margins' => ['top' => 5, 'right' => 5, 'bottom' => 5, 'left' => 5],
                    'card_margins' => ['top' => 2, 'right' => 2, 'bottom' => 2, 'left' => 2],
                ],
                'css_code' => '.card-inksaver {
    background: #ffffff;
    border: 1.5px dashed #000000;
    border-radius: 4px;
    padding: 6px 8px;
    font-family: Arial, Helvetica, sans-serif;
    color: #000000;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
}
.inksaver-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #000;
    padding-bottom: 4px;
    margin-bottom: 5px;
}
.inksaver-brand {
    font-weight: bold;
    font-size: 12px;
    text-transform: uppercase;
}
.inksaver-pkg {
    font-size: 10px;
    font-weight: bold;
}
.inksaver-body {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
}
.inksaver-cred {
    flex: 1;
    border: 1px solid #000;
    padding: 4px 6px;
    font-size: 11px;
}
.inksaver-qr {
    width: 48px;
    height: 48px;
}
.inksaver-qr img {
    width: 100%;
    height: 100%;
    display: block;
}
.inksaver-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #000;
    padding-top: 3px;
    margin-top: 4px;
    font-size: 10px;
}',
                'template_code' => '<div class="card-inksaver">
    <div class="inksaver-header">
        <span class="inksaver-brand">{{company.name}}</span>
        <span class="inksaver-pkg">{{package.name}}</span>
    </div>
    <div class="inksaver-body">
        <div class="inksaver-cred">
            <div>User: <b>{{voucher.username}}</b></div>
            <div>Pass: <b>{{voucher.password}}</b></div>
        </div>
        <div class="inksaver-qr">
            <img src="{qr($voucher.code, 90)}" alt="QR" />
        </div>
    </div>
    <div class="inksaver-footer">
        <b>{{package.price|currency}}</b>
        <span>Durasi: {{package.duration}}</span>
    </div>
</div>',
            ],
        ];
    }

    private function seedConnection(string $connection): void
    {
        $templates = $this->getTemplatesData();

        foreach ($templates as $item) {
            $template = VoucherTemplate::on($connection)->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'description' => $item['description'],
                    'is_system' => $item['is_system'],
                    'is_active' => $item['is_active'],
                    'is_default' => $item['is_default'],
                    'created_by' => 1,
                ]
            );

            // Version 1
            VoucherTemplateVersion::on($connection)->updateOrCreate(
                [
                    'voucher_template_id' => $template->id,
                    'version' => 1,
                ],
                [
                    'template_code' => $item['template_code'],
                    'css_code' => $item['css_code'],
                    'settings' => $item['settings'],
                    'created_by' => 1,
                ]
            );
        }
    }

    private function seedTenantSqlite(string $filePath): void
    {
        try {
            $pdo = new \PDO("sqlite:{$filePath}");
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Verify table exists
            $tableExists = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='voucher_templates'")->fetchColumn();
            if (!$tableExists) {
                return;
            }

            $templates = $this->getTemplatesData();
            foreach ($templates as $item) {
                $checkStmt = $pdo->prepare("SELECT id FROM voucher_templates WHERE slug = :slug LIMIT 1");
                $checkStmt->execute([':slug' => $item['slug']]);
                $existingId = $checkStmt->fetchColumn();

                $now = date('Y-m-d H:i:s');
                if ($existingId) {
                    $templateId = (int) $existingId;
                    $upStmt = $pdo->prepare("UPDATE voucher_templates SET name = :name, category = :category, description = :description, is_system = :is_system, is_active = :is_active, is_default = :is_default, updated_at = :updated_at WHERE id = :id");
                    $upStmt->execute([
                        ':name' => $item['name'],
                        ':category' => $item['category'],
                        ':description' => $item['description'],
                        ':is_system' => $item['is_system'] ? 1 : 0,
                        ':is_active' => $item['is_active'] ? 1 : 0,
                        ':is_default' => $item['is_default'] ? 1 : 0,
                        ':updated_at' => $now,
                        ':id' => $templateId,
                    ]);
                } else {
                    $inStmt = $pdo->prepare("INSERT INTO voucher_templates (name, slug, category, description, is_system, is_active, is_default, created_by, created_at, updated_at) VALUES (:name, :slug, :category, :description, :is_system, :is_active, :is_default, 1, :created_at, :updated_at)");
                    $inStmt->execute([
                        ':name' => $item['name'],
                        ':slug' => $item['slug'],
                        ':category' => $item['category'],
                        ':description' => $item['description'],
                        ':is_system' => $item['is_system'] ? 1 : 0,
                        ':is_active' => $item['is_active'] ? 1 : 0,
                        ':is_default' => $item['is_default'] ? 1 : 0,
                        ':created_at' => $now,
                        ':updated_at' => $now,
                    ]);
                    $templateId = (int) $pdo->lastInsertId();
                }

                // Check version
                $verCheck = $pdo->prepare("SELECT id FROM voucher_template_versions WHERE voucher_template_id = :tid AND version = 1 LIMIT 1");
                $verCheck->execute([':tid' => $templateId]);
                $verId = $verCheck->fetchColumn();

                $settingsJson = json_encode($item['settings']);
                if ($verId) {
                    $verUp = $pdo->prepare("UPDATE voucher_template_versions SET template_code = :code, css_code = :css, settings = :settings, updated_at = :updated_at WHERE id = :id");
                    $verUp->execute([
                        ':code' => $item['template_code'],
                        ':css' => $item['css_code'],
                        ':settings' => $settingsJson,
                        ':updated_at' => $now,
                        ':id' => $verId,
                    ]);
                } else {
                    $verIn = $pdo->prepare("INSERT INTO voucher_template_versions (voucher_template_id, version, template_code, css_code, settings, created_by, created_at, updated_at) VALUES (:tid, 1, :code, :css, :settings, 1, :created_at, :updated_at)");
                    $verIn->execute([
                        ':tid' => $templateId,
                        ':code' => $item['template_code'],
                        ':css' => $item['css_code'],
                        ':settings' => $settingsJson,
                        ':created_at' => $now,
                        ':updated_at' => $now,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Log or ignore tenant seed failure
        }
    }
}
