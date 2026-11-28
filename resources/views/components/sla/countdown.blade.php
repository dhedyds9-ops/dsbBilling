@props([
    'dueAt' => null,
    'startedAt' => null,
    'slaStatus' => 'normal',
    'isCompleted' => false,
    'compact' => false,
])

@php
    try {
        $dueCarbon = $dueAt ? \Carbon\Carbon::parse($dueAt) : null;
    } catch (\Throwable) {
        $dueCarbon = null;
    }
    try {
        $startCarbon = $startedAt ? \Carbon\Carbon::parse($startedAt) : null;
    } catch (\Throwable) {
        $startCarbon = null;
    }
    $dueUnix = $dueCarbon ? $dueCarbon->timestamp : 0;
    $startUnix = $startCarbon ? $startCarbon->timestamp : 0;
    $nowUnix = now()->timestamp;
@endphp

<div x-data="{
    dueUnix: {{ $dueUnix }},
    startUnix: {{ $startUnix }},
    serverOffset: {{ $nowUnix }} - Math.floor(Date.now() / 1000),
    isCompleted: {{ $isCompleted ? 'true' : 'false' }},
    status: '{{ $slaStatus }}',
    remainingText: '...',
    badgeColor: 'emerald',
    percent: 0,
    timer: null,

    init() {
        this.updateCountdown();
        if (!this.isCompleted && this.dueUnix > 0) {
            this.timer = setInterval(() => {
                this.updateCountdown();
            }, 1000);
        }
    },

    updateCountdown() {
        if (this.isCompleted) {
            this.remainingText = 'Selesai';
            this.badgeColor = this.status === 'breached' ? 'rose' : 'slate';
            return;
        }

        if (this.dueUnix === 0) {
            this.remainingText = '-';
            this.badgeColor = 'slate';
            return;
        }

        const currentServerTime = Math.floor(Date.now() / 1000) + this.serverOffset;
        const diffSeconds = this.dueUnix - currentServerTime;
        const totalSeconds = Math.max(1, this.dueUnix - this.startUnix);

        if (diffSeconds < 0) {
            this.status = 'breached';
            this.badgeColor = 'rose';
            const absSec = Math.abs(diffSeconds);
            const hrs = Math.floor(absSec / 3600);
            const mins = Math.floor((absSec % 3600) / 60);
            const secs = absSec % 60;
            this.remainingText = hrs > 0 
                ? `Terlambat ${hrs}j ${mins}m` 
                : `Terlambat ${mins}m ${secs}s`;
            this.percent = 100;
        } else {
            const hrs = Math.floor(diffSeconds / 3600);
            const mins = Math.floor((diffSeconds % 3600) / 60);
            const secs = diffSeconds % 60;
            this.percent = Math.min(100, Math.max(0, Math.floor(((totalSeconds - diffSeconds) / totalSeconds) * 100)));

            if (this.percent >= 80) {
                this.status = 'warning';
                this.badgeColor = 'amber';
            } else {
                this.status = 'normal';
                this.badgeColor = 'emerald';
            }

            this.remainingText = hrs > 0 
                ? `${hrs}j ${mins}m ${secs}s` 
                : `${mins}m ${secs}s`;
        }
    }
}" class="inline-flex items-center gap-1.5 font-mono text-xs font-semibold">
    <template x-if="badgeColor === 'emerald'">
        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20" title="SLA Normal (< 80%)">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span x-text="remainingText"></span>
            @if(!$compact)
                <span class="text-[10px] text-emerald-600/70 dark:text-emerald-400/70" x-text="'(' + percent + '%)'"></span>
            @endif
        </span>
    </template>
    <template x-if="badgeColor === 'amber'">
        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 font-bold" title="SLA Warning (>= 80%)">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
            <span x-text="remainingText"></span>
            @if(!$compact)
                <span class="text-[10px] text-amber-600/70 dark:text-amber-400/70" x-text="'(' + percent + '%)'"></span>
            @endif
        </span>
    </template>
    <template x-if="badgeColor === 'rose'">
        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 font-bold" title="SLA Breached (Terlambat)">
            <svg class="w-3.5 h-3.5 text-rose-500 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span x-text="remainingText"></span>
        </span>
    </template>
    <template x-if="badgeColor === 'slate'">
        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-500/10 text-slate-500 dark:text-slate-400 border border-slate-500/20" title="Selesai">
            <span x-text="remainingText"></span>
        </span>
    </template>
</div>
