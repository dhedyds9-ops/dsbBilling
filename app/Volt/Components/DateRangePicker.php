<?php

namespace App\Volt\Components;

use Livewire\Volt\Component;

new class extends Component {
    public string $selectedDate = '';
    public string $startDate = '';
    public string $endDate = '';
    public bool $showCalendar = false;
    public array $presets = [
        ['label' => 'Today', 'value' => 'today'],
        ['label' => 'Yesterday', 'value' => 'yesterday'],
        ['label' => 'Last 7 days', 'value' => 'last_7_days'],
        ['label' => 'Last 30 days', 'value' => 'last_30_days'],
        ['label' => 'This month', 'value' => 'this_month'],
        ['label' => 'Last month', 'value' => 'last_month'],
        ['label' => 'This year', 'value' => 'this_year'],
    ];

    public function applyPreset(string $preset)
    {
        $dates = $this->calculatePresetDates($preset);
        $this->startDate = $dates['start'];
        $this->endDate = $dates['end'];

        $this->dispatch('date-range-changed', [
            'start' => $this->startDate,
            'end' => $this->endDate,
        ])->to(DateRangePicker::class);

        $this->showCalendar = false;
    }

    public function calculatePresetDates(string $preset): array
    {
        $today = new \DateTime();

        return match ($preset) {
            'today' => [
                'start' => $today->format('Y-m-d'),
                'end' => $today->format('Y-m-d'),
            ],
            'yesterday' => [
                'start' => (clone $today)->modify('-1 day')->format('Y-m-d'),
                'end' => (clone $today)->modify('-1 day')->format('Y-m-d'),
            ],
            'last_7_days' => [
                'start' => (clone $today)->modify('-6 days')->format('Y-m-d'),
                'end' => $today->format('Y-m-d'),
            ],
            'last_30_days' => [
                'start' => (clone $today)->modify('-29 days')->format('Y-m-d'),
                'end' => $today->format('Y-m-d'),
            ],
            'this_month' => [
                'start' => (clone $today)->modify('first day of this month')->format('Y-m-d'),
                'end' => (clone $today)->modify('last day of this month')->format('Y-m-d'),
            ],
            'last_month' => [
                'start' => (clone $today)->modify('first day of last month')->format('Y-m-d'),
                'end' => (clone $today)->modify('last day of last month')->format('Y-m-d'),
            ],
            'this_year' => [
                'start' => (clone $today)->modify('first day of January')->format('Y-m-d'),
                'end' => (clone $today)->modify('last day of December')->format('Y-m-d'),
            ],
            default => [
                'start' => $today->format('Y-m-d'),
                'end' => $today->format('Y-m-d'),
            ],
        };
    }

    public function selectDate(string $date)
    {
        if (empty($this->startDate) || !empty($this->endDate)) {
            $this->startDate = $date;
            $this->endDate = '';
        } else {
            if ($date < $this->startDate) {
                $this->endDate = $this->startDate;
                $this->startDate = $date;
            } else {
                $this->endDate = $date;
            }

            $this->dispatch('date-range-changed', [
                'start' => $this->startDate,
                'end' => $this->endDate,
            ])->to(DateRangePicker::class);

            $this->showCalendar = false;
        }
    }
};

?>

<div class="relative" x-data="{ show: @entangle('showCalendar') }">
    <div class="flex items-center gap-2">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <input
                type="text"
                readonly
                value="{{ $startDate && $endDate ? $startDate . ' - ' . $endDate : ($startDate ?? 'Select date') }}"
                @click="show = !show"
                class="w-full pl-9 pr-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-sm cursor-pointer"
            />
        </div>
    </div>

    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-soft-lg p-4 min-w-[320px]"
        @click.away="show = false"
        style="display: none;"
    >
        <div class="flex gap-6">
            <div class="w-48 space-y-2">
                <h4 class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Quick Select</h4>
                @foreach($presets as $preset)
                    <button
                        type="button"
                        wire:click="applyPreset('{{ $preset['value'] }}')"
                        class="w-full px-3 py-2 text-left text-sm rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    >
                        {{ $preset['label'] }}
                    </button>
                @endforeach
            </div>

            <div class="flex-1 border-l border-slate-200 dark:border-slate-700 pl-4">
                <h4 class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase mb-3">Custom Range</h4>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Start</label>
                        <input
                            type="date"
                            wire:model.live="startDate"
                            class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900"
                        />
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">End</label>
                        <input
                            type="date"
                            wire:model.live="endDate"
                            class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900"
                        />
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="$emit('applyRange')"
                    class="w-full px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors"
                >
                    Apply Range
                </button>
            </div>
        </div>
    </div>
</div>
