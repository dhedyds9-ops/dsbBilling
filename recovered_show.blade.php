                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">TX Speed</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">RX Speed</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($interfaces as $interface)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">
                                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">settings_ethernet</span>
                                            </div>
                                            <span class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $interface['name'] ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 font-mono">{{ $interface['type'] ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(($interface['running'] ?? 'false') === 'true' || ($interface['status'] ?? '') === 'link-up')
                                            <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                                Connected
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                Disconnected
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600 dark:text-blue-400 font-mono">
                                        <span class="material-symbols-outlined notranslate text-[14px] align-middle mr-1" translate="no">arrow_upward</span>{{ number_format(($interface['tx-bps'] ?? 0) / 1000000, 2) }} Mbps
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                                        <span class="material-symbols-outlined notranslate text-[14px] align-middle mr-1" translate="no">arrow_downward</span>{{ number_format(($interface['rx-bps'] ?? 0) / 1000000, 2) }} Mbps
                                    </td>