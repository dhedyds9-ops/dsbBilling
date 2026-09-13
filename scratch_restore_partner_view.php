<?php
$file = 'resources/views/livewire/pengaturan/perusahaan/index.blade.php';
$content = file_get_contents($file);

$partnerIdentityForm = <<<BLADE
        </div>
      </div>
      
      <!-- IDENTITAS MITRA / PARTNER -->
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden mt-4">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30">
            <div class="font-semibold text-slate-900 dark:text-slate-100">Identitas Mitra / Partner</div>
            <div class="text-xs text-slate-500">Jika Anda bermitra dengan pihak lain dan ingin logo/nama mitra tampil di invoice</div>
          </div>
          <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Mitra</label>
                <input type="text" wire:model="company.partner_name" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="PT Mitra Sejahtera">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Telepon / HP Mitra</label>
                <input type="text" wire:model="company.partner_mobile" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="08xxx">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Email Mitra</label>
                <input type="email" wire:model="company.partner_email" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="mitra@domain.com">
            </div>
          </div>
      </div>
    </form>
BLADE;
$content = str_replace("        </div>\n      </div>\n    </form>", $partnerIdentityForm, $content);

$partnerLogoUpload = <<<BLADE
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Logo Mitra / Partner</label>
            <div class="flex items-start gap-3">
              <div class="w-24 h-24 rounded-lg border border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 flex items-center justify-center overflow-hidden">
              @if (\$company['partner_logo_url'] ?? false)
                <img src="{{ \$company['partner_logo_url'] }}" class="w-full h-full object-contain" alt="partner logo">
              @else
                <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V1a2 2 0 002-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
              @endif
              </div>
              <div class="flex-1">
                <input type="file" wire:model="partnerLogoFile" id="partnerLogoUpload" class="hidden">
                <label for="partnerLogoUpload" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-md cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>Pilih Logo Mitra</label>
                @if (\$company['partner_logo_url'] ?? false)
                  <button wire:click="resetPartnerLogo" type="button" class="ml-2 text-xs text-slate-500 hover:text-red-600">Hapus</button>
                @endif
                <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Format PNG/JPG · Max 2MB</div>
              </div>
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Cap / Stempel Perusahaan</label>
BLADE;
$content = str_replace("          <div>\n            <label class=\"block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1\">Cap / Stempel Perusahaan</label>", $partnerLogoUpload, $content);

file_put_contents($file, $content);
echo "Restored partner settings in view\n";
