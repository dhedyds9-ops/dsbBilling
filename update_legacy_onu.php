<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

$oldTable = <<<HTML
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-3 text-left font-semibold">Tipe</th>
                                <th class="px-6 py-3 text-left font-semibold">Model</th>
                                <th class="px-6 py-3 text-left font-semibold">Serial Number</th>
                                <th class="px-6 py-3 text-left font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach(\$devices as \$device)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-slate-600">{{ \$device['type'] }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ \$device['brand'] }} {{ \$device['model'] }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-900">{{ \$device['serial'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-lg {{ \$device['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst(\$device['status']) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
HTML;

$newTable = <<<HTML
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-3 text-left font-semibold">Perangkat</th>
                                <th class="px-6 py-3 text-left font-semibold">Topologi FTTH</th>
                                <th class="px-6 py-3 text-left font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach(\$devices as \$device)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ \$device['brand'] }} {{ \$device['model'] }}</div>
                                    <div class="font-mono text-xs text-slate-500 mt-1">SN: {{ \$device['serial'] }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if(isset(\$device['topology']))
                                        <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                            <div class="flex flex-col items-center">
                                                <span class="material-symbols-outlined text-slate-400" style="font-size: 16px;">dns</span>
                                                <span>{{ \$device['topology']['olt'] }}</span>
                                            </div>
                                            <span class="material-symbols-outlined text-slate-300" style="font-size: 14px;">arrow_forward</span>
                                            <div class="flex flex-col items-center">
                                                <span class="material-symbols-outlined text-slate-400" style="font-size: 16px;">lan</span>
                                                <span>PON: {{ \$device['topology']['pon_port'] }}</span>
                                            </div>
                                            <span class="material-symbols-outlined text-slate-300" style="font-size: 14px;">arrow_forward</span>
                                            <div class="flex flex-col items-center">
                                                <span class="material-symbols-outlined text-slate-400" style="font-size: 16px;">device_hub</span>
                                                <span>ODP: {{ \$device['topology']['odp'] }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">Topologi tidak tersedia</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-lg {{ \$device['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst(\$device['status']) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
HTML;

$content = str_replace($oldTable, $newTable, $content);
file_put_contents($file, $content);
echo "Updated legacy onu table.";
?>
