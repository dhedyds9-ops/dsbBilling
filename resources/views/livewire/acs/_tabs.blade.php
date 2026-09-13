@section('page_title')
<div class="flex items-center gap-3">
  <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">router</span>
  <span class="text-lg">GenieACS (TR-069)</span>
</div>
@endsection

@section('page_actions')
  @if(isset($actions))
    {{ $actions }}
  @else
    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium transition-colors">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">arrow_back</span>
        Dashboard Utama
    </a>
  @endif
@endsection

<div class="mb-5 border-b border-slate-200 dark:border-slate-700">
  <nav class="flex items-center gap-6 overflow-x-auto">
    @php
      $tabs = [
          ['route' => 'acs.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
          ['route' => 'acs.devices.index', 'label' => 'Devices', 'icon' => 'router'],
          ['route' => 'acs.tasks.index', 'label' => 'Task Queue', 'icon' => 'pending_actions'],
          ['route' => 'acs.alarms.index', 'label' => 'Alarms', 'icon' => 'warning'],
          ['route' => 'acs.firmware.index', 'label' => 'Firmware', 'icon' => 'system_update_alt'],
          ['route' => 'acs.settings', 'label' => 'Pengaturan', 'icon' => 'settings'],
      ];
    @endphp
    @foreach($tabs as $tab)
      @php
        $isActive = request()->routeIs(explode('.', $tab['route'])[0] . '.' . explode('.', $tab['route'])[1] . '.*') || request()->routeIs($tab['route']);
      @endphp
      <a href="{{ route($tab['route']) }}" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm inline-flex items-center gap-2 transition-colors {{ $isActive ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-500' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:border-slate-600 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:border-slate-600' }}">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">{{ $tab['icon'] }}</span>
        {{ $tab['label'] }}
      </a>
    @endforeach
  </nav>
</div>
