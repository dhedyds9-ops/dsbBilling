<div class="space-y-6">
    <x-admin.breadcrumbs :breadcrumbs="$this->breadcrumbs" />
    <h1 class="text-2xl font-bold text-slate-900">ACS Dashboard</h1>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-base.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Online Devices</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['online_devices'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </x-base.card>
        <x-base.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Offline Devices</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['offline_devices'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </x-base.card>
        <x-base.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Pending Tasks</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['pending_tasks'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </x-base.card>
        <x-base.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Active Alarms</p>
                    <p class="text-2xl font-bold text-orange-600 mt-1">{{ $stats['active_alarms'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </x-base.card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Devices --}}
        <x-base.card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-slate-900">Recent Devices</h2>
                <a href="{{ route('acs.devices.index') }}" class="text-sm text-primary-600 hover:text-primary-700">View All</a>
            </div>
            <div class="space-y-3">
                @forelse($recentDevices ?? [] as $device)
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold text-sm">
                                {{ substr($device->serial_number ?? 'D', 0, 1) }}
                            </div>
                            <div>
                                <p class="font-medium text-slate-900">{{ $device->serial_number ?? '-' }}</p>
                                <p class="text-xs text-slate-500">{{ $device->model ?? '-' }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            @if($device->status === 'online') bg-green-100 text-green-600
                            @else bg-red-100 text-red-600
                            @endif
                        ">
                            {{ ucfirst($device->status) }}
                        </span>
                    </div>
                @empty
                    <p class="text-slate-500 text-center py-4">No recent devices</p>
                @endforelse
            </div>
        </x-base.card>

        {{-- Recent Tasks --}}
        <x-base.card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-slate-900">Recent Tasks</h2>
                <a href="{{ route('acs.tasks.index') }}" class="text-sm text-primary-600 hover:text-primary-700">View All</a>
            </div>
            <div class="space-y-3">
                @forelse($recentTasks ?? [] as $task)
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                        <div>
                            <p class="font-medium text-slate-900">{{ ucfirst($task->type) }}</p>
                            <p class="text-xs text-slate-500">{{ $task->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
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
                @empty
                    <p class="text-slate-500 text-center py-4">No recent tasks</p>
                @endforelse
            </div>
        </x-base.card>
    </div>
</div>
