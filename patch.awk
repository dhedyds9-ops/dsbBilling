BEGIN { }
{
    print $0
    if (NR == 288) {
        print "<th class="px-6 py-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>"
    }
    if (NR == 303) {
        print "<td class="px-6 py-4 whitespace-nowrap text-right"><button wire:click="disconnectPpp('{{ $session['name'] }}')" wire:confirm="Yakin ingin kick user ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Kick"><span class="material-symbols-outlined notranslate text-[18px]" translate="no">power_settings_new</span></button></td>"
    }
    if (NR == 408) {
        print "<th class="px-6 py-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>"
    }
    if (NR == 424) {
        print "<td class="px-6 py-4 whitespace-nowrap text-right"><button wire:click="disconnectHotspot('{{ $session['user'] ?? $session['mac-address'] }}')" wire:confirm="Yakin ingin kick user ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Kick"><span class="material-symbols-outlined notranslate text-[18px]" translate="no">power_settings_new</span></button></td>"
    }
}
