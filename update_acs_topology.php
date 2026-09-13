<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

$search = <<<HTML
                                        <div class="text-xs text-slate-400 mt-2 flex items-center gap-1">
                                            <span class="material-symbols-outlined notranslate" style="font-size:14px" translate="no">update</span>
                                            Last Inform: {{ \$service->acsDevice->last_inform ? \$service->acsDevice->last_inform->diffForHumans() : '-' }}
                                        </div>
HTML;

$replace = <<<HTML
                                        <div class="text-xs text-slate-400 mt-2 flex items-center gap-1">
                                            <span class="material-symbols-outlined notranslate" style="font-size:14px" translate="no">update</span>
                                            Last Inform: {{ \$service->acsDevice->last_inform ? \$service->acsDevice->last_inform->diffForHumans() : '-' }}
                                        </div>
                                        
                                        @if(\$service->onu)
                                        <div class="mt-4 pt-4 border-t border-slate-200">
                                            <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Topologi Jaringan</div>
                                            <div class="flex items-center gap-2 text-xs font-medium text-slate-700">
                                                <div class="flex items-center gap-1.5 bg-slate-100 px-2.5 py-1.5 rounded-lg border border-slate-200">
                                                    <span class="material-symbols-outlined text-slate-400" style="font-size: 16px;">dns</span>
                                                    <span>{{ \$service->onu->olt->name ?? 'N/A' }}</span>
                                                </div>
                                                <span class="material-symbols-outlined text-slate-300" style="font-size: 14px;">arrow_forward</span>
                                                <div class="flex items-center gap-1.5 bg-slate-100 px-2.5 py-1.5 rounded-lg border border-slate-200">
                                                    <span class="material-symbols-outlined text-slate-400" style="font-size: 16px;">lan</span>
                                                    <span>PON: {{ \$service->onu->formatted_pon_port ?? \$service->onu->ponPort->name ?? '-' }}</span>
                                                </div>
                                                <span class="material-symbols-outlined text-slate-300" style="font-size: 14px;">arrow_forward</span>
                                                <div class="flex items-center gap-1.5 bg-slate-100 px-2.5 py-1.5 rounded-lg border border-slate-200">
                                                    <span class="material-symbols-outlined text-slate-400" style="font-size: 16px;">device_hub</span>
                                                    <span>ODP: {{ \$service->onu->odp->name ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
HTML;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added topology to ACS router card.";
?>
