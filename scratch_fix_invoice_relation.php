<?php
$file = 'resources/views/livewire/billing/payment/index.blade.php';
$content = file_get_contents($file);

// Replace invoice column logic
$oldCol = <<<BLADE
                                @if(\$payment->invoice)
                                    <a href="{{ route('billing.invoices.show', \$payment->invoice->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ \$payment->invoice->invoice_number }}
                                    </a>
                                @else
BLADE;
$newCol = <<<BLADE
                                @if(\$payment->invoices->count() > 0)
                                    <a href="{{ route('billing.invoices.show', \$payment->invoices->first()->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ \$payment->invoices->first()->invoice_number }}
                                    </a>
                                @else
BLADE;
$content = str_replace($oldCol, $newCol, $content);

// Replace Action button logic
$oldAction = <<<BLADE
                                @if(\$payment->invoice)
                                    <a href="{{ route('billing.invoices.show', \$payment->invoice->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 dark:text-blue-400 transition-colors" title="Print Invoice">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">print</span>
                                    </a>
                                @else
BLADE;
$newAction = <<<BLADE
                                @if(\$payment->invoices->count() > 0)
                                    <a href="{{ route('billing.invoices.show', \$payment->invoices->first()->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 dark:text-blue-400 transition-colors" title="Print Invoice">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">print</span>
                                    </a>
                                @else
BLADE;
$content = str_replace($oldAction, $newAction, $content);

file_put_contents($file, $content);
echo "Fixed invoice relation to use invoices->first()\n";
