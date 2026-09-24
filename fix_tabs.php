<?php
$content = <<<'HTML'
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5 border-b border-slate-200 dark:border-slate-700">
  <div class="flex items-center gap-3 py-2">
    @section('page_title', 'GenieACS (TR-069)')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">router</span>
    <span class="text-lg font-semibold text-slate-900 dark:text-slate-100">GenieACS (TR-069)</span>
  </div>
  
  <div class="py-2">
    @if(isset($actions))
      {!! $actions !!}
    @else
      <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium transition-colors">
          <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">arrow_back</span>
          Dashboard Utama
      </a>
    @endif
  </div>
</div>
HTML;
file_put_contents('resources/views/livewire/acs/_tabs.blade.php', $content);
echo "Restored _tabs.blade.php!\n";
