<?php

namespace App\Volt\Components\Admin;

use Livewire\Volt\Component;

new class extends Component {
    public array $widgets = [];
    public array $availableWidgets = [];
    public bool $editMode = false;

    public function mount()
    {
        $this->availableWidgets = $this->getAvailableWidgets();
    }

    public function toggleEditMode()
    {
        $this->editMode = !$this->editMode;
    }

    public function addWidget(string $widgetId)
    {
        $widget = collect($this->availableWidgets)->firstWhere('id', $widgetId);

        if ($widget) {
            $this->widgets[] = [
                'id' => uniqid(),
                'widget_id' => $widgetId,
                'config' => $widget['defaultConfig'] ?? [],
            ];
        }
    }

    public function removeWidget(string $widgetInstanceId)
    {
        $this->widgets = array_filter($this->widgets, fn($w) => $w['id'] !== $widgetInstanceId);
    }

    public function updateWidgetOrder(array $order)
    {
        $reordered = [];
        foreach ($order as $id) {
            $widget = collect($this->widgets)->firstWhere('id', $id);
            if ($widget) {
                $reordered[] = $widget;
            }
        }
        $this->widgets = $reordered;
    }

    public function getAvailableWidgets(): array
    {
        return [
            [
                'id' => 'stat-card',
                'name' => 'Stat Card',
                'description' => 'Display a key metric',
                'icon' => 'chart-bar',
                'defaultConfig' => ['title' => '', 'value' => 0],
            ],
            [
                'id' => 'chart',
                'name' => 'Chart',
                'description' => 'Display a chart',
                'icon' => 'chart-line',
                'defaultConfig' => ['type' => 'line'],
            ],
            [
                'id' => 'recent-activity',
                'name' => 'Recent Activity',
                'description' => 'Show recent activities',
                'icon' => 'clock',
                'defaultConfig' => ['limit' => 5],
            ],
            [
                'id' => 'todo-list',
                'name' => 'Todo List',
                'description' => 'Quick todo items',
                'icon' => 'check-circle',
                'defaultConfig' => [],
            ],
            [
                'id' => 'quick-actions',
                'name' => 'Quick Actions',
                'description' => 'Common action buttons',
                'icon' => 'lightning-bolt',
                'defaultConfig' => [],
            ],
            [
                'id' => 'system-status',
                'name' => 'System Status',
                'description' => 'Current system health',
                'icon' => 'server',
                'defaultConfig' => [],
            ],
        ];
    }
};

?>

<div class="widget-container" x-data="{
    editMode: @entangle('editMode'),
    widgets: @entangle('widgets'),
    availableWidgets: @entangle('availableWidgets'),
    dragging: null,
    dragOver: null,
}">
    {{-- Widget Toolbar --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Dashboard Widgets</h2>

        <div class="flex items-center gap-2">
            @if($editMode)
                <x-button size="sm" variant="ghost" @click="editMode = false">
                    Done Editing
                </x-button>
            @else
                <x-button size="sm" variant="ghost" @click="editMode = true">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Edit Widgets
                </x-button>
            @endif
        </div>
    </div>

    {{-- Widget Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($widgets as $widget)
            <div
                class="widget-item bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-soft"
                :class="{ 'opacity-50': dragging === '{{ $widget['id'] }}' }"
                draggable="editMode"
                @dragstart="dragging = '{{ $widget['id'] }}'"
                @dragend="dragging = null"
                @dragover.prevent="dragOver = '{{ $widget['id'] }}'"
            >
                {{-- Widget Header --}}
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <x-icon :name="collect($availableWidgets)->firstWhere('id', $widget['widget_id'])['icon'] ?? 'cube'" class="w-5 h-5 text-slate-400" />
                        <span class="font-medium text-slate-900 dark:text-white">
                            {{ collect($availableWidgets)->firstWhere('id', $widget['widget_id'])['name'] ?? 'Widget' }}
                        </span>
                    </div>

                    @if($editMode)
                        <button
                            wire:click="removeWidget('{{ $widget['id'] }}')"
                            class="p-1 rounded hover:bg-danger-100 dark:hover:bg-danger-900/30 text-danger-600 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- Widget Content --}}
                <div class="widget-content min-h-[100px]">
                    @switch($widget['widget_id'])
                        @case('stat-card')
                            <x-display.stat-card
                                label="Total Customers"
                                value="12,345"
                                trend="5.2"
                                icon="users"
                            />
                        @break

                        @case('chart')
                            <div class="h-32 flex items-center justify-center bg-slate-50 dark:bg-slate-800 rounded-lg">
                                <span class="text-sm text-slate-400">Chart Placeholder</span>
                            </div>
                        @break

                        @case('recent-activity')
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="w-2 h-2 rounded-full bg-success-500"></span>
                                    <span class="text-slate-600 dark:text-slate-400">Customer #1234 payment received</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                                    <span class="text-slate-600 dark:text-slate-400">New ticket created</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="w-2 h-2 rounded-full bg-warning-500"></span>
                                    <span class="text-slate-600 dark:text-slate-400">Network alert resolved</span>
                                </div>
                            </div>
                        @break

                        @case('todo-list')
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" class="rounded border-slate-300 text-primary-600" />
                                    <span class="text-slate-600 dark:text-slate-400">Review OLT-01 alerts</span>
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" class="rounded border-slate-300 text-primary-600" />
                                    <span class="text-slate-600 dark:text-slate-400">Approve pending invoices</span>
                                </label>
                            </div>
                        @break

                        @case('quick-actions')
                            <div class="grid grid-cols-2 gap-2">
                                <x-button size="sm" variant="outline">New Customer</x-button>
                                <x-button size="sm" variant="outline">New Ticket</x-button>
                                <x-button size="sm" variant="outline">View Reports</x-button>
                                <x-button size="sm" variant="outline">Settings</x-button>
                            </div>
                        @break

                        @case('system-status')
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-600 dark:text-slate-400">API Status</span>
                                    <x-badge variant="success" size="sm">Online</x-badge>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-600 dark:text-slate-400">Database</span>
                                    <x-badge variant="success" size="sm">Connected</x-badge>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-600 dark:text-slate-400">Queue</span>
                                    <x-badge variant="warning" size="sm">3 Pending</x-badge>
                                </div>
                            </div>
                        @break

                        @default
                            <div class="h-24 flex items-center justify-center bg-slate-50 dark:bg-slate-800 rounded-lg">
                                <span class="text-sm text-slate-400">Widget content</span>
                            </div>
                    @endswitch
                </div>
            </div>
        @empty
            @if($editMode)
                @foreach($availableWidgets as $widget)
                    <div
                        class="bg-white dark:bg-slate-900 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-700 p-4 text-center cursor-pointer hover:border-primary-500 transition-colors"
                        @click="addWidget('{{ $widget['id'] }}')"
                    >
                        <x-icon :name="$widget['icon']" class="w-8 h-8 mx-auto mb-2 text-slate-400" />
                        <p class="font-medium text-slate-900 dark:text-white">{{ $widget['name'] }}</p>
                        <p class="text-sm text-slate-500">{{ $widget['description'] }}</p>
                    </div>
                @endforeach
            @else
                <div class="col-span-full py-12 text-center">
                    <svg class="w-12 h-12 mx-auto mb-4 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    <p class="text-slate-500 dark:text-slate-400">No widgets added yet</p>
                    <x-button size="sm" variant="primary" class="mt-4" @click="editMode = true">
                        Add Widgets
                    </x-button>
                </div>
            @endif
        @endforelse
    </div>
</div>
