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
                'name' => 'MSRadius Template 107 (Dynamic Color)',
                'slug' => 'msradius-template-107-dynamic',
                'category' => VoucherTemplateCategory::Classic->value,
                'description' => 'Template voucher MSRadius 107 dengan sudut badge harga melengkung, kotak kode voucher tebal, kuota & durasi, instruksi WiFi, serta badge WA dan domain login dengan aksen warna dinamis otomatis sesuai nominal harga.',
                'is_system' => false,
                'is_active' => true,
                'is_default' => true,
                'settings' => [
                    'paper_size' => 'A4',
                    'grid_preset' => '3x6',
                    'page_margins' => ['top' => 5, 'right' => 5, 'bottom' => 5, 'left' => 5],
                    'card_margins' => ['top' => 2, 'right' => 2, 'bottom' => 2, 'left' => 2],
                ],
                'css_code' => '.card-107 {
  width: 220px;
  height: 122px;
  background: #ffffff;
  border-radius: 4px;
  box-sizing: border-box;
  position: relative;
  overflow: hidden;
  font-family: Arial, Helvetica, sans-serif;
  margin: 3px;
  display: inline-block;
  vertical-align: top;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.card-107-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 3px 0 0 5px;
}

.card-107-logo-box {
  display: flex;
  align-items: center;
  gap: 3px;
  max-width: 130px;
}

.card-107-logo {
  height: 18px;
  max-width: 70px;
  object-fit: contain;
}

.card-107-brand {
  font-size: 11px;
  font-weight: 800;
  color: #0284c7;
  letter-spacing: -0.2px;
}

.card-107-price-badge {
  color: #ffffff;
  font-size: 13px;
  font-weight: 900;
  padding: 2px 10px 3px 12px;
  border-bottom-left-radius: 12px;
  letter-spacing: 0.3px;
  box-shadow: -1px 1px 2px rgba(0,0,0,0.15);
}

.card-107-kode-title {
  font-size: 9px;
  font-weight: 800;
  color: #1e293b;
  letter-spacing: 0.8px;
  padding-left: 8px;
  margin-top: 1px;
}

.card-107-body {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 2px 6px;
  gap: 6px;
}

.card-107-code-box {
  flex: 1.2;
  background: #ffffff;
  border-radius: 4px;
  font-size: 15px;
  font-weight: 900;
  font-family: "Courier New", Courier, monospace;
  text-align: center;
  padding: 4px 2px;
  letter-spacing: 0.5px;
  line-height: 1;
}

.card-107-dur-box {
  flex: 0.9;
  text-align: center;
  line-height: 1.1;
}

.card-107-dur-title {
  font-size: 14px;
  font-weight: 900;
  color: #0f172a;
}

.card-107-dur-sub {
  font-size: 8px;
  color: #64748b;
  font-weight: 600;
}

.card-107-dur-quota {
  font-size: 8.5px;
  color: #0f172a;
  font-weight: 700;
}

.card-107-notes {
  text-align: center;
  font-size: 7.5px;
  font-weight: 700;
  color: #475569;
  line-height: 1.15;
  margin-top: 2px;
}

.card-107-subnotes {
  font-size: 7px;
  font-weight: normal;
  color: #64748b;
}

.card-107-footer {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 2px 4px 2px 4px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

.card-107-wa-pill {
  background: #10b981;
  color: #ffffff;
  font-size: 7.5px;
  font-weight: 800;
  padding: 1.5px 5px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 2px;
}

.card-107-wa-icon {
  font-size: 8px;
}

.card-107-login-pill {
  display: flex;
  align-items: center;
  gap: 2px;
  font-size: 7.5px;
  font-weight: 800;
  color: #0f172a;
}

.card-107-triangle {
  width: 0;
  height: 0;
  border-top: 4px solid transparent;
  border-bottom: 4px solid transparent;
  border-left: 6px solid #00a2ff;
  display: inline-block;
}

.card-107-chrome-icon {
  font-size: 8px;
}

.card-107-login-url {
  font-family: monospace;
}',
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
<div class="card-107" style="display:inline-block; width:220px; height:122px; border:1.5px solid {$color}; box-sizing:border-box; background:#ffffff; position:relative; overflow:hidden; vertical-align:top; margin:3px; font-family:Arial,Helvetica,sans-serif;">
  <!-- Header: Logo & Badge Harga -->
  <div class="card-107-header" style="display:flex; justify-content:space-between; align-items:flex-start; padding:3px 0 0 5px;">
    <div class="card-107-logo-box" style="display:flex; align-items:center; gap:3px; max-width:130px; height:20px; overflow:hidden;">
      {if $company.logo}
        <img src="{{company.logo}}" alt="Logo" class="card-107-logo" style="max-height:18px; max-width:70px; width:auto; height:auto; object-fit:contain; display:inline-block; vertical-align:middle;" onerror="this.style.display=\'none\'">
      {/if}
      <span class="card-107-brand" style="font-size:11px; font-weight:800; color:#0284c7; letter-spacing:-0.2px;">{if $company.name}{{company.name}}{else}WINETS.ID{/if}</span>
    </div>
    <div class="card-107-price-badge" style="background:{$color}; color:#ffffff; font-size:13px; font-weight:900; padding:2px 10px 3px 12px; border-bottom-left-radius:12px; letter-spacing:0.3px; box-shadow:-1px 1px 2px rgba(0,0,0,0.15); line-height:1.2;">
      Rp. {{voucher.price|number_format(0, \',\', \'.\')}}
    </div>
  </div>

  <!-- Label Kode -->
  <div class="card-107-kode-title" style="font-size:9px; font-weight:800; color:#1e293b; letter-spacing:0.8px; padding-left:8px; margin-top:1px;">KODE VOUCHER</div>

  <!-- Body: Kotak Kode di kiri & Durasi di kanan -->
  <div class="card-107-body" style="display:flex; align-items:center; justify-content:space-between; padding:2px 6px; gap:6px;">
    <div class="card-107-code-box" style="flex:1.2; background:#ffffff; border-radius:4px; font-size:15px; font-weight:900; font-family:\'Courier New\',Courier,monospace; text-align:center; padding:4px 2px; letter-spacing:0.5px; line-height:1; border:2px solid {$color}; color:{$color};">
      {{voucher.code}}
    </div>
    <div class="card-107-dur-box" style="flex:0.9; text-align:center; line-height:1.1;">
      <div class="card-107-dur-title" style="font-size:14px; font-weight:900; color:#0f172a;">{if $package.duration}{{package.duration}}{elseif $package.name}{{package.name}}{else}6 JAM{/if}</div>
      <div class="card-107-dur-sub" style="font-size:8px; color:#64748b; font-weight:600;">Masa Pakai</div>
      <div class="card-107-dur-quota" style="font-size:8.5px; color:#0f172a; font-weight:700;">Kuota {if $package.quota}{{package.quota}}{else}Unlimited{/if}</div>
    </div>
  </div>

  <!-- Petunjuk Sambungan -->
  <div class="card-107-notes" style="text-align:center; font-size:7.5px; font-weight:700; color:#475569; line-height:1.15; margin-top:2px;">
    Sambungkan Ke WiFi {if $company.name}{{company.name}}{else}Winets.id{/if}<br>
    <span class="card-107-subnotes" style="font-size:7px; font-weight:normal; color:#64748b;">jika tidak muncul otomatis buka google</span>
  </div>

  <!-- Footer Bar -->
  <div class="card-107-footer" style="position:absolute; bottom:0; left:0; right:0; display:flex; align-items:center; justify-content:space-between; padding:2px 4px 2px 4px; background:#f8fafc; border-top:1px solid #e2e8f0;">
    <div class="card-107-wa-pill" style="background:#10b981; color:#ffffff; font-size:7.5px; font-weight:800; padding:1.5px 5px; border-radius:10px; display:flex; align-items:center; gap:2px;">
      <span class="card-107-wa-icon">&#9742;</span> {if $company.phone}{{company.phone}}{else}083139935920{/if}
    </div>
    <div class="card-107-login-pill" style="display:flex; align-items:center; gap:2px; font-size:7.5px; font-weight:800; color:#0f172a;">
      <span class="card-107-triangle" style="width:0; height:0; border-top:4px solid transparent; border-bottom:4px solid transparent; border-left:6px solid {$color}; display:inline-block;"></span>
      <span class="card-107-chrome-icon">&#127760;</span>
      <span class="card-107-login-url" style="font-family:monospace;">{if $voucher.login_url}{{voucher.login_url}}{else}http://winets.id{/if}</span>
    </div>
  </div>
</div>',
            ],
            [
                'name' => 'Voucher MSRadius Dynamic Color',
                'slug' => 'voucher-msradius-color',
                'category' => VoucherTemplateCategory::Classic->value,
                'description' => 'Template voucher dinamis bergaya MSRadius / Mikhmon Card 107 dengan warna otomatis sesuai nominal harga (Rp 2rb - 70rb), logo, dan QR Code login.',
                'is_system' => false,
                'is_active' => true,
                'is_default' => false,
                'settings' => [
                    'paper_size' => 'A4',
                    'grid_preset' => '3x6',
                    'page_margins' => ['top' => 5, 'right' => 5, 'bottom' => 5, 'left' => 5],
                    'card_margins' => ['top' => 2, 'right' => 2, 'bottom' => 2, 'left' => 2],
                ],
                'css_code' => '.card-107 {
  width: 220px;
  height: 122px;
  background: #ffffff;
  border-radius: 4px;
  box-sizing: border-box;
  position: relative;
  overflow: hidden;
  font-family: Arial, Helvetica, sans-serif;
  margin: 3px;
  display: inline-block;
  vertical-align: top;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.card-107-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 3px 0 0 5px;
}

.card-107-logo-box {
  display: flex;
  align-items: center;
  gap: 3px;
  max-width: 130px;
}

.card-107-logo {
  height: 18px;
  max-width: 70px;
  object-fit: contain;
}

.card-107-brand {
  font-size: 11px;
  font-weight: 800;
  color: #0284c7;
  letter-spacing: -0.2px;
}

.card-107-price-badge {
  color: #ffffff;
  font-size: 13px;
  font-weight: 900;
  padding: 2px 10px 3px 12px;
  border-bottom-left-radius: 12px;
  letter-spacing: 0.3px;
  box-shadow: -1px 1px 2px rgba(0,0,0,0.15);
}

.card-107-kode-title {
  font-size: 9px;
  font-weight: 800;
  color: #1e293b;
  letter-spacing: 0.8px;
  padding-left: 8px;
  margin-top: 1px;
}

.card-107-body {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 2px 6px;
  gap: 6px;
}

.card-107-code-box {
  flex: 1.2;
  background: #ffffff;
  border-radius: 4px;
  font-size: 15px;
  font-weight: 900;
  font-family: "Courier New", Courier, monospace;
  text-align: center;
  padding: 4px 2px;
  letter-spacing: 0.5px;
  line-height: 1;
}

.card-107-dur-box {
  flex: 0.9;
  text-align: center;
  line-height: 1.1;
}

.card-107-dur-title {
  font-size: 14px;
  font-weight: 900;
  color: #0f172a;
}

.card-107-dur-sub {
  font-size: 8px;
  color: #64748b;
  font-weight: 600;
}

.card-107-dur-quota {
  font-size: 8.5px;
  color: #0f172a;
  font-weight: 700;
}

.card-107-notes {
  text-align: center;
  font-size: 7.5px;
  font-weight: 700;
  color: #475569;
  line-height: 1.15;
  margin-top: 2px;
}

.card-107-subnotes {
  font-size: 7px;
  font-weight: normal;
  color: #64748b;
}

.card-107-footer {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 2px 4px 2px 4px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

.card-107-wa-pill {
  background: #10b981;
  color: #ffffff;
  font-size: 7.5px;
  font-weight: 800;
  padding: 1.5px 5px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 2px;
}

.card-107-wa-icon {
  font-size: 8px;
}

.card-107-login-pill {
  display: flex;
  align-items: center;
  gap: 2px;
  font-size: 7.5px;
  font-weight: 800;
  color: #0f172a;
}

.card-107-triangle {
  width: 0;
  height: 0;
  border-top: 4px solid transparent;
  border-bottom: 4px solid transparent;
  border-left: 6px solid #00a2ff;
  display: inline-block;
}

.card-107-chrome-icon {
  font-size: 8px;
}

.card-107-login-url {
  font-family: monospace;
}',
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
<div class="card-107" style="display:inline-block; width:220px; height:122px; border:1.5px solid {$color}; box-sizing:border-box; background:#ffffff; position:relative; overflow:hidden; vertical-align:top; margin:3px; font-family:Arial,Helvetica,sans-serif;">
  <!-- Header: Logo & Badge Harga -->
  <div class="card-107-header" style="display:flex; justify-content:space-between; align-items:flex-start; padding:3px 0 0 5px;">
    <div class="card-107-logo-box" style="display:flex; align-items:center; gap:3px; max-width:130px; height:20px; overflow:hidden;">
      {if $company.logo}
        <img src="{{company.logo}}" alt="Logo" class="card-107-logo" style="max-height:18px; max-width:70px; width:auto; height:auto; object-fit:contain; display:inline-block; vertical-align:middle;" onerror="this.style.display=\'none\'">
      {/if}
      <span class="card-107-brand" style="font-size:11px; font-weight:800; color:#0284c7; letter-spacing:-0.2px;">{if $company.name}{{company.name}}{else}WINETS.ID{/if}</span>
    </div>
    <div class="card-107-price-badge" style="background:{$color}; color:#ffffff; font-size:13px; font-weight:900; padding:2px 10px 3px 12px; border-bottom-left-radius:12px; letter-spacing:0.3px; box-shadow:-1px 1px 2px rgba(0,0,0,0.15); line-height:1.2;">
      Rp. {{voucher.price|number_format(0, \',\', \'.\')}}
    </div>
  </div>

  <!-- Label Kode -->
  <div class="card-107-kode-title" style="font-size:9px; font-weight:800; color:#1e293b; letter-spacing:0.8px; padding-left:8px; margin-top:1px;">KODE VOUCHER</div>

  <!-- Body: Kotak Kode di kiri & Durasi di kanan -->
  <div class="card-107-body" style="display:flex; align-items:center; justify-content:space-between; padding:2px 6px; gap:6px;">
    <div class="card-107-code-box" style="flex:1.2; background:#ffffff; border-radius:4px; font-size:15px; font-weight:900; font-family:\'Courier New\',Courier,monospace; text-align:center; padding:4px 2px; letter-spacing:0.5px; line-height:1; border:2px solid {$color}; color:{$color};">
      {{voucher.code}}
    </div>
    <div class="card-107-dur-box" style="flex:0.9; text-align:center; line-height:1.1;">
      <div class="card-107-dur-title" style="font-size:14px; font-weight:900; color:#0f172a;">{if $package.duration}{{package.duration}}{elseif $package.name}{{package.name}}{else}6 JAM{/if}</div>
      <div class="card-107-dur-sub" style="font-size:8px; color:#64748b; font-weight:600;">Masa Pakai</div>
      <div class="card-107-dur-quota" style="font-size:8.5px; color:#0f172a; font-weight:700;">Kuota {if $package.quota}{{package.quota}}{else}Unlimited{/if}</div>
    </div>
  </div>

  <!-- Petunjuk Sambungan -->
  <div class="card-107-notes" style="text-align:center; font-size:7.5px; font-weight:700; color:#475569; line-height:1.15; margin-top:2px;">
    Sambungkan Ke WiFi {if $company.name}{{company.name}}{else}Winets.id{/if}<br>
    <span class="card-107-subnotes" style="font-size:7px; font-weight:normal; color:#64748b;">jika tidak muncul otomatis buka google</span>
  </div>

  <!-- Footer Bar -->
  <div class="card-107-footer" style="position:absolute; bottom:0; left:0; right:0; display:flex; align-items:center; justify-content:space-between; padding:2px 4px 2px 4px; background:#f8fafc; border-top:1px solid #e2e8f0;">
    <div class="card-107-wa-pill" style="background:#10b981; color:#ffffff; font-size:7.5px; font-weight:800; padding:1.5px 5px; border-radius:10px; display:flex; align-items:center; gap:2px;">
      <span class="card-107-wa-icon">&#9742;</span> {if $company.phone}{{company.phone}}{else}083139935920{/if}
    </div>
    <div class="card-107-login-pill" style="display:flex; align-items:center; gap:2px; font-size:7.5px; font-weight:800; color:#0f172a;">
      <span class="card-107-triangle" style="width:0; height:0; border-top:4px solid transparent; border-bottom:4px solid transparent; border-left:6px solid {$color}; display:inline-block;"></span>
      <span class="card-107-chrome-icon">&#127760;</span>
      <span class="card-107-login-url" style="font-family:monospace;">{if $voucher.login_url}{{voucher.login_url}}{else}http://winets.id{/if}</span>
    </div>
  </div>
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
