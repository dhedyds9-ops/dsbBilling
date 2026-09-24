<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5 border-b border-slate-200 dark:border-slate-700">
  <div class="flex flex-wrap items-center gap-1 sm:gap-4 py-2">
    @section('page_title', 'GenieACS (TR-069)')
    
    <a href="{{ route('acs.dashboard') }}" class="px-3 py-2 text-sm font-medium border-b-2 {{ request()->routeIs('acs.dashboard') ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300' }}">
        <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined notranslate" style="font-size:18px">dashboard</span>
            Dashboard
        </div>
    </a>
    
    <a href="{{ route('acs.devices.index') }}" class="px-3 py-2 text-sm font-medium border-b-2 {{ request()->routeIs('acs.devices.*') ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300' }}">
        <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined notranslate" style="font-size:18px">router</span>
            Devices
        </div>
    </a>

    <a href="{{ route('acs.tasks.index') }}" class="px-3 py-2 text-sm font-medium border-b-2 {{ request()->routeIs('acs.tasks.*') ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300' }}">
        <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined notranslate" style="font-size:18px">pending_actions</span>
            Tasks
        </div>
    </a>
    
    <a href="{{ route('acs.alarms.index') }}" class="px-3 py-2 text-sm font-medium border-b-2 {{ request()->routeIs('acs.alarms.*') ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300' }}">
        <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined notranslate" style="font-size:18px">warning</span>
            Alarms
        </div>
    </a>

    <a href="{{ route('acs.firmware.index') }}" class="px-3 py-2 text-sm font-medium border-b-2 {{ request()->routeIs('acs.firmware.*') ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300' }}">
        <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined notranslate" style="font-size:18px">system_update</span>
            Firmware
        </div>
    </a>

    <a href="{{ route('acs.settings') }}" class="px-3 py-2 text-sm font-medium border-b-2 {{ request()->routeIs('acs.settings') ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300' }}">
        <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined notranslate" style="font-size:18px">settings</span>
            Pengaturan
        </div>
    </a>
  </div>
  
  <div class="py-2">
    @if(isset($actions))
      {!! $actions !!}
    @endif
  </div>
</div>
