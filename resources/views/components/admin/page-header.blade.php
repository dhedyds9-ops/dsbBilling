{{--
/**
 * Page Header — Material 3
 *
 * Slot/Props:
 *  - title       : Judul halaman (wajib)
 *  - subtitle    : Deskripsi singkat dibawah judul (opsional)
 *  - icon        : Material Symbols name
 *  - iconBg      : primary / success / warning / danger / info / secondary
 *  - {{ $actions }} : Slot untuk tombol aksi (export, create, filter, dll)
 *  - {{ $metadata }} : Slot opsional di bawah subtitle (badges, status info, dll)
 *
 * Usage:
 *   <x-admin.page-header title="Data Pelanggan" subtitle="Kelola seluruh pelanggan aktif" icon="users">
 *       <x-slot:actions>
 *           <x-base.button icon="add" size="md">Tambah Pelanggan</x-base.button>
 *       </x-slot:actions>
 *   </x-admin.page-header>
 */
--}}

@props([
    'title' => 'Untitled',
    'subtitle' => null,
    'icon' => null,
    'iconBg' => 'primary',
])

@php
    $iconBgMap = [
        'primary'   => 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300',
        'secondary' => 'bg-secondary-100 text-secondary-700 dark:bg-secondary-900/30 dark:text-secondary-300',
        'success'   => 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-300',
        'warning'   => 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-300',
        'danger'    => 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-300',
        'info'      => 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-300',
    ];
    $iconBgClass = $iconBgMap[$iconBg] ?? $iconBgMap['primary'];
@endphp

<section class="relative overflow-hidden">
    <div
        class="relative
               bg-ds-surface
               border border-slate-200 dark:border-slate-700 rounded-2xl shadow-soft-sm"
    >
        {{-- Decorative gradient accent --}}
        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 opacity-[0.07]
                   bg-[radial-gradient(circle_at_top_right,var(--ds-primary),transparent_55%),
                      radial-gradient(circle_at_bottom_left,var(--ds-secondary),transparent_55%)]"
        ></div>

        <div class="relative p-5 sm:p-6 flex flex-col gap-5">
            <div
                class="flex flex-col md:flex-row md:items-start md:justify-between
                       gap-5"
            >
                {{-- Title cluster --}}
                <div class="flex items-start gap-4 min-w-0">
                    @if($icon)
                        <div
                            class="shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center
                                   {{ $iconBgClass }} shadow-soft-sm"
                        >
                            <span class="material-symbols-outlined fill ms-28">{{ $icon }}</span>
                        </div>
                    @endif

                    <div class="min-w-0 flex-1 space-y-2">
                        <h1
                            class="text-xl sm:text-2xl font-extrabold tracking-tight
                                   text-ds-on-surface leading-[1.2]"
                        >
                            {{ $title }}
                        </h1>

                        @if($subtitle || isset($metadata))
                            <div class="space-y-1.5">
                                @if($subtitle)
                                    <p class="text-sm text-ds-on-surface-variant leading-relaxed">
                                        {{ $subtitle }}
                                    </p>
                                @endif
                                @isset($metadata)
                                    <div>{{ $metadata }}</div>
                                @endisset
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Actions cluster --}}
                @isset($actions)
                    <div
                        class="flex items-center gap-2 flex-wrap
                               md:justify-end md:shrink-0"
                    >
                        {{ $actions }}
                    </div>
                @endisset
            </div>

            {{-- Extra bottom toolbar (optional slot) --}}
            @isset($toolbar)
                <div
                    class="pt-4 mt-2 border-t border-slate-200 dark:border-slate-700 flex flex-wrap items-center gap-3"
                >
                    {{ $toolbar }}
                </div>
            @endisset
        </div>
    </div>
</section>






