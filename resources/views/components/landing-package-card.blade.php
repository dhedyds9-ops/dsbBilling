@props(['package', 'index'])
<div class="pricing-card-wrapper flex {{ $index > 0 ? 'sm:mt-0 mt-4' : '' }} {{ $index > 1 ? 'lg:mt-0' : '' }}">
    <div class="glass-card {{ $index == 1 ? 'best-seller-card bg-white dark:bg-slate-800 sm:scale-[1.05] shadow-2xl relative z-10 border-2' : 'hover:scale-[1.01] transition-all duration-300 border-2 border-transparent' }} p-6 sm:p-8 rounded-[22px] sm:rounded-[24px] flex flex-col w-full" style="{{ $index == 1 ? 'border-color: var(--ds-primary); box-shadow: 0 30px 60px -20px rgba(30,136,229,0.32);' : '' }}">
        @if($index == 1)
            <div class="price-best-seller-badge">Best Seller</div>
        @endif
        <p class="uppercase text-[12px] font-bold mb-3 tracking-[0.14em] text-ds-primary">
            {{ $package->service_type === 'pppoe' ? 'Rumahan' : ($package->service_type === 'hotspot' ? 'Member' : 'Voucher') }}
        </p>
        <h3 class="font-bold text-[20px] mb-1" style="color: var(--ds-on-surface);">{{ $package->name }}</h3>
        
        <div class="flex items-baseline gap-1 mb-5 sm:mb-6">
            <span class="text-[15px]" style="color: var(--ds-on-surface-variant);">Rp</span>
            <span class="font-black text-[30px] sm:text-[32px] leading-none" style="color: var(--ds-on-surface);">{{ number_format($package->base_price, 0, ',', '.') }}</span>
            <span class="text-[13px] sm:text-[14px]" style="color: var(--ds-on-surface-variant);">
                @if($package->package_type === 'time_based')
                    / {{ $package->duration_value }} {{ $package->duration_unit === 'hours' ? 'Jam' : 'Hari' }}
                @elseif($package->package_type === 'quota_based')
                    / {{ $package->quota_value }} {{ $package->quota_unit }}
                @else
                    / {{ $package->validity_value ?? 30 }} {{ $package->validity_unit === 'hours' ? 'Jam' : ($package->validity_unit === 'months' ? 'Bulan' : 'Hari') }}
                @endif
            </span>
        </div>
        
        <ul class="space-y-2.5 sm:space-y-3 mb-8 sm:mb-10 flex-grow text-[15px] sm:text-[16px]" style="color: var(--ds-on-surface);">
            <li class="flex items-center gap-2"><span class="material-symbols-outlined fill text-ds-secondary text-[20px] sm:text-[22px] flex-shrink-0">check_circle</span>Speed up to {{ $package->download_speed }} Mbps</li>
            @if($package->description)
                <li class="flex items-center gap-2"><span class="material-symbols-outlined fill text-ds-secondary text-[20px] sm:text-[22px] flex-shrink-0">check_circle</span>{{ Str::limit($package->description, 50) }}</li>
            @endif
            <li class="flex items-center gap-2"><span class="material-symbols-outlined fill text-ds-secondary text-[20px] sm:text-[22px] flex-shrink-0">check_circle</span>
                @if($package->package_type === 'quota_based')
                    Kuota {{ $package->quota_value }} {{ $package->quota_unit }}
                @else
                    {{ $package->fup_enabled ? 'FUP ' . $package->fup_threshold . 'GB' : 'Unlimited Quota' }}
                @endif
            </li>
            @if($package->max_devices > 0)
                <li class="flex items-center gap-2"><span class="material-symbols-outlined fill text-ds-secondary text-[20px] sm:text-[22px] flex-shrink-0">check_circle</span>{{ $package->max_devices }} Perangkat</li>
            @endif
        </ul>
        <a href="{{ route('customer.login') }}" class="w-full py-3 {{ $index == 1 ? 'btn-primary-solid text-white shadow-lg border-transparent' : 'border-2 text-ds-primary hover:bg-ds-primary hover:text-white' }} font-bold rounded-xl text-center transition active:scale-95 min-h-[48px] flex items-center justify-center" style="{{ $index != 1 ? 'border-color: var(--ds-primary);' : '' }}">Pilih Paket</a>
    </div>
</div>






