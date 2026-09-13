<?php
$src = 'D:/dsBilling/resources/views/livewire/isp/hotspot-user/create.blade.php';
$dst = 'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create-hotspot.blade.php';
$content = file_get_contents($src);

// Replace route('isp.hotspot-users.index') with route('reseller-portal.customers.hotspot')
$content = str_replace("route('isp.hotspot-users.index')", "route('reseller-portal.customers.hotspot')", $content);

// In Section 4: Afiliasi, remove reseller_id
$search = <<<'EOT'
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Reseller / Pemilik</label>
                            <select wire:model="reseller_id" 
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all">
                                <option value="">Kantor Pusat (HQ)</option>
                                @foreach($resellers as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->email }})</option>
                                @endforeach
                            </select>
                            @error('reseller_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
EOT;

$content = str_replace($search, '', $content);

file_put_contents($dst, $content);
echo "Created create-hotspot.blade.php\n";
?>
