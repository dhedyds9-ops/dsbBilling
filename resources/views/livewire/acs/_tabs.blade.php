<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5 border-b {{ $isNocLayout ? 'noc-border' : 'border-slate-200 dark:border-slate-700' }}">
  <div class="flex flex-wrap items-center gap-1 sm:gap-4 py-2">
    @section('page_title', 'GenieACS (TR-069)')
    
    <a href="{{ route($isNocLayout ? 'noc.acs.dashboard' : 'acs.dashboard') }}" class="px-3 py-2 text-sm font-medium border-b-2 {{ request()->routeIs('*.acs.dashboard', 'acs.dashboard') ? ($isNocLayout ? 'noc-border noc-text' : 'border-indigo-500 text-indigo-600 dark:text-indigo-400') : ($isNocLayout ? 'border-transparent text-gray-500 hover:text-gray-300 hover:border-gray-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300') }}">
        <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined notranslate" style="font-size:18px">dashboard</span>
            Dashboard
        </div>
    </a>
    
    <a href="{{ route($isNocLayout ? 'noc.acs.devices.index' : 'acs.devices.index') }}" class="px-3 py-2 text-sm font-medium border-b-2 {{ request()->routeIs('*.acs.devices.*', 'acs.devices.*') ? ($isNocLayout ? 'noc-border noc-text' : 'border-indigo-500 text-indigo-600 dark:text-indigo-400') : ($isNocLayout ? 'border-transparent text-gray-500 hover:text-gray-300 hover:border-gray-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300') }}">
        <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined notranslate" style="font-size:18px">router</span>
            Devices
        </div>
    </a>

    <a href="{{ route($isNocLayout ? 'noc.acs.tasks.index' : 'acs.tasks.index') }}" class="px-3 py-2 text-sm font-medium border-b-2 {{ request()->routeIs('*.acs.tasks.*', 'acs.tasks.*') ? ($isNocLayout ? 'noc-border noc-text' : 'border-indigo-500 text-indigo-600 dark:text-indigo-400') : ($isNocLayout ? 'border-transparent text-gray-500 hover:text-gray-300 hover:border-gray-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300') }}">
        <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined notranslate" style="font-size:18px">pending_actions</span>
            Tasks
        </div>
    </a>
    
    <a href="{{ route($isNocLayout ? 'noc.acs.alarms.index' : 'acs.alarms.index') }}" class="px-3 py-2 text-sm font-medium border-b-2 {{ request()->routeIs('*.acs.alarms.*', 'acs.alarms.*') ? ($isNocLayout ? 'noc-border noc-text' : 'border-indigo-500 text-indigo-600 dark:text-indigo-400') : ($isNocLayout ? 'border-transparent text-gray-500 hover:text-gray-300 hover:border-gray-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300') }}">
        <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined notranslate" style="font-size:18px">warning</span>
            Alarms
        </div>
    </a>

    <a href="{{ route($isNocLayout ? 'noc.acs.firmware.index' : 'acs.firmware.index') }}" class="px-3 py-2 text-sm font-medium border-b-2 {{ request()->routeIs('*.acs.firmware.*', 'acs.firmware.*') ? ($isNocLayout ? 'noc-border noc-text' : 'border-indigo-500 text-indigo-600 dark:text-indigo-400') : ($isNocLayout ? 'border-transparent text-gray-500 hover:text-gray-300 hover:border-gray-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300') }}">
        <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined notranslate" style="font-size:18px">system_update</span>
            Firmware
        </div>
    </a>

    <a href="{{ route($isNocLayout ? 'noc.acs.settings' : 'acs.settings') }}" class="px-3 py-2 text-sm font-medium border-b-2 {{ request()->routeIs('*.acs.settings', 'acs.settings') ? ($isNocLayout ? 'noc-border noc-text' : 'border-indigo-500 text-indigo-600 dark:text-indigo-400') : ($isNocLayout ? 'border-transparent text-gray-500 hover:text-gray-300 hover:border-gray-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300') }}">
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
