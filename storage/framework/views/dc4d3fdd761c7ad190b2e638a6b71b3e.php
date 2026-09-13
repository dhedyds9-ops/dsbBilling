<div>
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
    <?php $__env->startSection('page_title'); ?>
  <div>
      <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Pengaturan Perusahaan</h1>
      <div class="text-xs text-slate-500 dark:text-slate-400">Identitas, kontak, dan informasi legal perusahaan billing ISP</div>
    </div>
    <?php $__env->stopSection(); ?>
    <div class="flex items-center gap-2">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($savedStatus === 'saved'): ?>
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 text-xs font-medium">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">check</span>
        Berhasil disimpan
      </span>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      <button wire:click="testEmail" class="px-3 py-1.5 text-sm border border-slate-200 dark:border-slate-700 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 inline-flex items-center gap-1.5 transition-colors">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">mail</span>
        Test Email
      </button>
      <button wire:click="save" class="px-4 py-1.5 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium inline-flex items-center gap-1.5 transition-colors">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">save</span>
        Simpan Pengaturan
      </button>
    </div>
  </div>

  <div class="p-4 grid grid-cols-1 xl:grid-cols-3 gap-4">
    <form class="xl:col-span-2 space-y-4">
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30">
          <div class="font-semibold text-slate-900 dark:text-slate-100">Identitas Perusahaan</div>
        </div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
          <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Perusahaan *</label><input type="text" wire:model="company.name" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-slate-900 dark:text-slate-100" placeholder="PT Internet Cepat Terpercaya"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Badan Hukum</label><input type="text" wire:model="company.legal_name" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="PT / CV / UD / Yayasan"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">NPWP</label><input type="text" wire:model="company.npwp" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="00.000.000.0-000.000"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">NIB (Nomor Induk Berusaha)</label><input type="text" wire:model="company.nib" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="123456789012345"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">SIUP / NIB Perdagangan</label><input type="text" wire:model="company.siup" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"></div>
          <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">SPPL / Izin Penyelenggaraan</label><input type="text" wire:model="company.sppl" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="No. SPPL Kominfo"></div>
          <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Alamat Kantor *</label><textarea wire:model="company.address" rows="2" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Jl. Raya ..."></textarea></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">RT / RW</label><div class="flex gap-2"><input type="text" wire:model="company.rt" class="flex-1 px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="RT"><input type="text" wire:model="company.rw" class="flex-1 px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="RW"></div></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Kelurahan / Desa</label><input type="text" wire:model="company.village" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Kecamatan</label><input type="text" wire:model="company.district" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Kota / Kabupaten</label><input type="text" wire:model="company.city" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Provinsi</label><input type="text" wire:model="company.province" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Kode Pos</label><input type="text" wire:model="company.postal_code" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30">
          <div class="font-semibold text-slate-900 dark:text-slate-100">Kontak & Komunikasi</div>
        </div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Telepon</label><input type="text" wire:model="company.phone" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="(021) xxx xxxx"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Mobile / WhatsApp</label><input type="text" wire:model="company.mobile" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="08xx-xxxx-xxxx"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Email Umum</label><input type="email" wire:model="company.email" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="info@perusahaan.co.id"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Website</label><input type="url" wire:model="company.website" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="https://www.perusahaan.co.id"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Email Billing</label><input type="email" wire:model="company.billing_email" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="billing@perusahaan.co.id"></div>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30">
          <div class="font-semibold text-slate-900 dark:text-slate-100">Struktur Organisasi</div>
        </div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Direktur Utama / CEO</label><input type="text" wire:model="company.ceo_name" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Direktur / Operational</label><input type="text" wire:model="company.director_name" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Kepala Keuangan</label><input type="text" wire:model="company.finance_name" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Kepala NOC</label><input type="text" wire:model="company.head_noc_name" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Berdiri</label><input type="date" wire:model="company.established_date" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"></div>
          <div class="md:col-span-3"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Jam Operasional Layanan</label><input type="text" wire:model="company.operational_hours" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Senin - Sabtu 08:00 - 20:00 WIB · Minggu 09:00 - 15:00 WIB"></div>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30">
          <div class="font-semibold text-slate-900 dark:text-slate-100">Informasi Perbankan (Virtual Account / Transfer Bank)</div>
        </div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
          <div>
          <div class="mb-2">
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Bank 1</label>
            <input type="text" wire:model="company.bank_1_name" class="w-full mb-1 px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Bank BCA">
            <input type="text" wire:model="company.bank_1_account" class="w-full mb-1 px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="No. Rekening">
            <input type="text" wire:model="company.bank_1_holder" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Atas Nama">
          </div>
        </div>
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Bank 2</label>
            <input type="text" wire:model="company.bank_2_name" class="w-full mb-1 px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Bank BRI">
            <input type="text" wire:model="company.bank_2_account" class="w-full mb-1 px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="No. Rekening">
            <input type="text" wire:model="company.bank_2_holder" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Atas Nama">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Bank 3 / KPP Pajak</label>
            <input type="text" wire:model="company.bank_3_name" class="w-full mb-1 px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Bank Mandiri - KPP Pajak">
            <input type="text" wire:model="company.bank_3_account" class="w-full mb-1 px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="No. Rekening">
            <input type="text" wire:model="company.bank_3_holder" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Atas Nama">
          </div>
          <div class="md:col-span-3"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Kantor Pelayanan Pajak (KPP) Terdaftar</label><input type="text" wire:model="company.tax_office" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="KPP Pratama Jakarta ..."></div>
        </div>
      </div>
      
      <!-- IDENTITAS MITRA / PARTNER -->
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden mt-4">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30">
            <div class="font-semibold text-slate-900 dark:text-slate-100">Identitas Mitra / Partner</div>
            <div class="text-xs text-slate-500 dark:text-slate-400">Jika Anda bermitra dengan pihak lain dan ingin logo/nama mitra tampil di invoice</div>
          </div>
          <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Mitra</label>
                <input type="text" wire:model="company.partner_name" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-slate-900 dark:text-slate-100" placeholder="PT Mitra Sejahtera">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Telepon / HP Mitra</label>
                <input type="text" wire:model="company.partner_mobile" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-slate-900 dark:text-slate-100" placeholder="08xxx">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Email Mitra</label>
                <input type="email" wire:model="company.partner_email" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-slate-900 dark:text-slate-100" placeholder="mitra@domain.com">
            </div>
          </div>
      </div>
    </form>

    <aside class="space-y-4">
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30">
          <div class="font-semibold text-slate-900 dark:text-slate-100">Logo, Stampel & TTD Digital</div>
        </div>
        <div class="p-4 space-y-3 text-sm">
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Logo Perusahaan</label>
            <div class="flex items-start gap-3">
              <div class="w-24 h-24 rounded-lg border border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 flex items-center justify-center overflow-hidden">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($company['logo_url'] ?? false): ?>
                <img src="<?php echo e($company['logo_url']); ?>" class="w-full h-full object-contain" alt="logo">
              <?php else: ?>
                <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 dark:text-slate-400" translate="no" style="font-size:32px">image</span>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="flex-1">
              <input type="file" wire:model="logoUpload" id="logoUpload" class="hidden dark:bg-slate-900 dark:text-slate-100">
              <label for="logoUpload" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-md cursor-pointer hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">upload</span>Pilih Logo
              </label>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($company['logo_url'] ?? false): ?>
                <button wire:click="resetLogo" type="button" class="ml-2 text-xs text-slate-500 dark:text-slate-400 hover:text-red-600">Hapus</button>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Format PNG/JPG · Max 2MB · Resolusi ≥ 512x512</div>
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Logo Mitra / Partner</label>
            <div class="flex items-start gap-3">
              <div class="w-24 h-24 rounded-lg border border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 flex items-center justify-center overflow-hidden">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($company['partner_logo_url'] ?? false): ?>
                <img src="<?php echo e($company['partner_logo_url']); ?>" class="w-full h-full object-contain" alt="partner logo">
              <?php else: ?>
                <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 dark:text-slate-400" translate="no" style="font-size:32px">handshake</span>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </div>
              <div class="flex-1">
                <input type="file" wire:model="partnerLogoFile" id="partnerLogoUpload" class="hidden dark:bg-slate-900 dark:text-slate-100">
                <label for="partnerLogoUpload" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-md cursor-pointer hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
                  <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">upload</span>Pilih Logo Mitra
                </label>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($company['partner_logo_url'] ?? false): ?>
                  <button wire:click="resetPartnerLogo" type="button" class="ml-2 text-xs text-slate-500 dark:text-slate-400 hover:text-red-600">Hapus</button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Format PNG/JPG ? Max 2MB</div>
              </div>
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Cap / Stempel Perusahaan</label>
            <div class="flex items-start gap-3">
              <div class="w-24 h-24 rounded-lg border border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 flex items-center justify-center overflow-hidden">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($company['stamp_url'] ?? false): ?>
                <img src="<?php echo e($company['stamp_url']); ?>" class="w-full h-full object-contain" alt="stamp">
              <?php else: ?>
                <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 dark:text-slate-400" translate="no" style="font-size:32px">verified</span>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="flex-1">
              <input type="file" wire:model="stampUpload" id="stampUpload" class="hidden dark:bg-slate-900 dark:text-slate-100">
              <label for="stampUpload" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-md cursor-pointer hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">upload</span>Pilih Cap
              </label>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($company['stamp_url'] ?? false): ?>
                <button wire:click="resetStamp" type="button" class="ml-2 text-xs text-slate-500 dark:text-slate-400 hover:text-red-600">Hapus</button>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Tanda Tangan Digital</label>
            <textarea wire:model="company.signature" rows="3" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs dark:bg-slate-900 dark:text-slate-100" placeholder="/Nama Direktur/  atau &#10;Teks TTD ASCII"></textarea>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30">
          <div class="font-semibold text-slate-900 dark:text-slate-100">Preview Quick Look</div>
        </div>
        <div class="p-4 space-y-2 text-xs text-slate-600 dark:text-slate-300">
          <div class="flex items-center gap-3 pb-2 border-b border-slate-100 dark:border-slate-700">
            <div class="w-12 h-12 rounded bg-indigo-600 text-white flex items-center justify-center font-bold">
              <?php echo e(strtoupper(substr($company['name'] ?? 'D', 0, 1))); ?>

            </div>
            <div>
              <div class="font-bold text-sm text-slate-900 dark:text-slate-100"><?php echo e($company['name'] ?? 'Nama Perusahaan'); ?></div>
              <div class="text-slate-500 dark:text-slate-400"><?php echo e($company['legal'] ?? ''); ?> · NPWP <?php echo e($company['npwp'] ?? '-'); ?></div>
            </div>
          </div>
          <div>📞 <?php echo e($company['phone'] ?? '-'); ?></div>
          <div>📱 <?php echo e($company['mobile'] ?? '-'); ?></div>
          <div>✉️ <?php echo e($company['email'] ?? '-'); ?></div>
          <div>🌐 <?php echo e($company['website'] ?? '-'); ?></div>
          <div>📍 <?php echo e($company['alamat'] ?? '-'); ?></div>
          <div class="pt-2 mt-2 border-t border-slate-100 dark:border-slate-700 text-[11px] text-slate-500 dark:text-slate-400">Preview di atas adalah tampilan di faktur, kwitansi, dan surat keluar.</div>
        </div>
      </div>
    </aside>
  </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\pengaturan\perusahaan\index.blade.php ENDPATH**/ ?>