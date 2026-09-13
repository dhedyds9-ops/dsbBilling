file_blade = 'D:/dsBilling/resources/views/livewire/admin/payroll/show.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

import re

search = """<p class="text-[13px] sm:text-sm font-semibold text-slate-700 print:text-black mt-1">Periode: {{ \\Carbon\\Carbon::createFromDate($payroll->period_year, $payroll->period_month, 1)->translatedFormat('F Y') }}</p>"""

replace = """<p class="text-[13px] sm:text-sm font-semibold text-slate-700 print:text-black mt-1">
                        Periode: 
                        @if($payroll->period_start && $payroll->period_end)
                            {{ \\Carbon\\Carbon::parse($payroll->period_start)->translatedFormat('d M Y') }} - {{ \\Carbon\\Carbon::parse($payroll->period_end)->translatedFormat('d M Y') }}
                        @else
                            {{ \\Carbon\\Carbon::createFromDate($payroll->period_year, $payroll->period_month, 1)->translatedFormat('F Y') }}
                        @endif
                    </p>"""

if search in content:
    content = content.replace(search, replace)
    with open(file_blade, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Show blade modified")
else:
    print("Search block not found.")
