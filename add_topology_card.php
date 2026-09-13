<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

// Add a standalone Topology Card before Legacy ONU
$topologyCard = <<<HTML
            {{-- TOPOLOGI JARINGAN (Standalone) --}}
            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-emerald-500" translate="no" style="font-size:20px">share</span>
                        Topologi Jaringan FTTH
                    </h3>
                </x-slot>
                
                <div class="space-y-4">
                    @php \$hasAnyTopology = false; @endphp
                    @foreach(\$customer->customerServices as \$service)
                        @if(\$service->onu)
                            @php \$hasAnyTopology = true; @endphp
                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                                <div class="text-xs font-bold text-slate-500 mb-3 uppercase tracking-wider">Koneksi Layanan: {{ \$service->serviceProfile->name ?? 'Layanan Utama' }}</div>
                                <div class="flex flex-wrap items-center gap-3 text-sm font-medium text-slate-700">
                                    <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-slate-200 shadow-sm">
                                        <span class="material-symbols-outlined text-slate-400" style="font-size: 20px;">dns</span>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] text-slate-400 leading-none">OLT SERVER</span>
                                            <span>{{ \$service->onu->olt->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <span class="material-symbols-outlined text-slate-300" style="font-size: 20px;">arrow_forward</span>
                                    <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-slate-200 shadow-sm">
                                        <span class="material-symbols-outlined text-slate-400" style="font-size: 20px;">lan</span>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] text-slate-400 leading-none">PON PORT</span>
                                            <span>{{ \$service->onu->formatted_pon_port ?? \$service->onu->ponPort->name ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <span class="material-symbols-outlined text-slate-300" style="font-size: 20px;">arrow_forward</span>
                                    <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-slate-200 shadow-sm">
                                        <span class="material-symbols-outlined text-slate-400" style="font-size: 20px;">device_hub</span>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] text-slate-400 leading-none">ODP BOX</span>
                                            <span>{{ \$service->onu->odp->name ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    
                    @if(!\$hasAnyTopology)
                        <div class="text-center py-8 text-slate-500 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                            <span class="material-symbols-outlined notranslate text-4xl text-slate-300 mb-2" translate="no">link_off</span>
                            <p class="text-sm font-medium text-slate-600">Pelanggan belum dipetakan ke ODP / OLT</p>
                            <p class="text-xs mt-1 text-slate-400">Teknisi dapat melakukan pemetaan melalui modul Jaringan.</p>
                        </div>
                    @endif
                </div>
            </x-base.card>

HTML;

$search = "{{-- LEGACY ONU DEVICES --}}";
$content = str_replace($search, $topologyCard . "\n            " . $search, $content);
file_put_contents($file, $content);
echo "Added standalone topology card.";
?>
