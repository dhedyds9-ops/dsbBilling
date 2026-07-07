<div class="space-y-6">
    <x-admin.breadcrumbs :breadcrumbs="$this->breadcrumbs" />
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $device->serial_number ?? 'Device Detail' }}</h1>
            <p class="mt-1 text-sm text-slate-500">Detail perangkat ACS</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('acs.devices.edit', $device->id) }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                Edit
            </a>
            <a href="{{ route('acs.devices.index') }}" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-900">Kembali</a>
        </div>
    </div>

    {{-- Device Info Card --}}
    <x-base.card>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <p class="text-sm text-slate-500">Serial Number</p>
                <p class="text-lg font-semibold text-slate-900 mt-1">{{ $device->serial_number ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-500">MAC Address</p>
                <p class="text-lg font-semibold text-slate-900 mt-1">{{ $device->mac_address ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-500">Model</p>
                <p class="text-lg font-semibold text-slate-900 mt-1">{{ $device->model ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-500">Status</p>
                <span class="px-3 py-1 text-xs font-medium rounded-full 
                    @if($device->status === 'online') bg-green-100 text-green-600
                    @else bg-red-100 text-red-600
                    @endif
                ">
                    {{ ucfirst($device->status) }}
                </span>
            </div>
            <div>
                <p class="text-sm text-slate-500">IP Address</p>
                <p class="text-lg font-semibold text-slate-900 mt-1">{{ $device->ip_address ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-500">Firmware Version</p>
                <p class="text-lg font-semibold text-slate-900 mt-1">{{ $device->firmware_version ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-500">Last Inform</p>
                <p class="text-lg font-semibold text-slate-900 mt-1">{{ $device->last_inform?->format('d/m/Y H:i') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-500">Customer</p>
                <p class="text-lg font-semibold text-slate-900 mt-1">{{ $device->customerService?->name ?? '-' }}</p>
            </div>
        </div>
    </x-base.card>

    {{-- Tabs for Tasks, Alarms, Logs --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Tasks --}}
        <x-base.card>
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Recent Tasks</h2>
            <div class="space-y-3">
                @forelse($device->tasks ?? [] as $task)
                    <div class="p-3 bg-slate-50 rounded-lg">
                        <p class="font-medium text-slate-900">{{ ucfirst($task->type) }}</p>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-xs text-slate-500">{{ $task->created_at?->format('d/m/Y H:i') }}</span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                @if($task->status === 'completed') bg-green-100 text-green-600
                                @elseif($task->status === 'running') bg-blue-100 text-blue-600
                                @elseif($task->status === 'failed') bg-red-100 text-red-600
                                @else bg-yellow-100 text-yellow-600
                                @endif
                            ">
                                {{ ucfirst($task->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 text-center py-4">No tasks</p>
                @endforelse
            </div>
        </x-base.card>

        {{-- Recent Alarms --}}
        <x-base.card>
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Recent Alarms</h2>
            <div class="space-y-3">
                @forelse($device->alarms ?? [] as $alarm)
                    <div class="p-3 bg-slate-50 rounded-lg">
                        <p class="font-medium text-slate-900">{{ $alarm->message }}</p>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-xs text-slate-500">{{ $alarm->triggered_at?->format('d/m/Y H:i') }}</span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                @if($alarm->severity === 'critical') bg-red-100 text-red-600
                                @elseif($alarm->severity === 'warning') bg-yellow-100 text-yellow-600
                                @else bg-blue-100 text-blue-600
                                @endif
                            ">
                                {{ ucfirst($alarm->severity) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 text-center py-4">No alarms</p>
                @endforelse
            </div>
        </x-base.card>

        {{-- Recent Logs --}}
        <x-base.card>
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Recent Logs</h2>
            <div class="space-y-3">
                @forelse($device->logs ?? [] as $log)
                    <div class="p-3 bg-slate-50 rounded-lg">
                        <p class="font-medium text-slate-900">{{ $log->message }}</p>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-xs text-slate-500">{{ $log->created_at?->format('d/m/Y H:i') }}</span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-600">
                                {{ ucfirst($log->type) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 text-center py-4">No logs</p>
                @endforelse
            </div>
        </x-base.card>
    </div>
</div>
