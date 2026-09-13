file_blade = 'D:/dsBilling/resources/views/livewire/admin/payroll/show.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

import re

# Add PHP block at top to define variables
search0 = """<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">"""
replace0 = """@php
    $isTech = request()->routeIs('technician.*');
    $rowClass = $isTech ? 'flex-col' : 'flex-col md:flex-row';
    $gridClass = $isTech ? 'grid-cols-1' : 'grid-cols-1 md:grid-cols-2';
    $textRightClass = $isTech ? 'text-left mt-4' : 'text-left md:text-right w-full md:w-auto';
    $paddingClass = $isTech ? 'p-4' : 'p-4 sm:p-8';
@endphp
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">"""
if search0 in content:
    content = content.replace(search0, replace0)

search1 = """<div class="p-8 print:p-0 text-slate-800">"""
replace1 = """<div class="{{ $paddingClass }} print:p-0 text-slate-800">"""
if search1 in content:
    content = content.replace(search1, replace1)

search2 = """<div class="flex flex-col md:flex-row justify-between items-start border-b-2 border-slate-800 pb-4 mb-6 print:flex-row print:border-black gap-4">"""
replace2 = """<div class="flex {{ $rowClass }} justify-between items-start border-b-2 border-slate-800 pb-4 mb-6 print:flex-row print:border-black gap-4">"""
if search2 in content:
    content = content.replace(search2, replace2)

search3 = """<div class="text-left md:text-right print:text-right w-full md:w-auto">"""
replace3 = """<div class="{{ $textRightClass }} print:text-right print:w-auto">"""
if search3 in content:
    content = content.replace(search3, replace3)

search4 = """<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 text-[13px] md:text-sm print:grid-cols-2 print:text-black">"""
replace4 = """<div class="grid {{ $gridClass }} gap-4 mb-8 text-[13px] md:text-sm print:grid-cols-2 print:text-black">"""
if search4 in content:
    content = content.replace(search4, replace4)

search5 = """<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 print:grid-cols-2">"""
replace5 = """<div class="grid {{ $gridClass }} gap-8 mb-8 print:grid-cols-2">"""
if search5 in content:
    content = content.replace(search5, replace5)

search6 = """<div class="bg-slate-50 border border-slate-200 rounded-lg p-5 mb-8 flex flex-col sm:flex-row justify-between sm:items-center print:bg-transparent print:border-t-2 print:border-b-2 print:border-l-0 print:border-r-0 print:border-black print:rounded-none">"""
replace6 = """<div class="bg-slate-50 border border-slate-200 rounded-lg p-5 mb-8 flex {{ $rowClass }} justify-between sm:items-center print:bg-transparent print:border-t-2 print:border-b-2 print:border-l-0 print:border-r-0 print:border-black print:rounded-none">"""
if search6 in content:
    content = content.replace(search6, replace6)

search7 = """<div class="flex flex-col sm:flex-row justify-between mt-12 text-[13px] sm:text-sm print:flex-row print:text-black">"""
replace7 = """<div class="flex {{ $rowClass }} justify-between mt-12 text-[13px] sm:text-sm print:flex-row print:text-black">"""
if search7 in content:
    content = content.replace(search7, replace7)

search8 = """<div class="w-full sm:w-1/3 mb-8 sm:mb-0 print:mb-0">"""
replace8 = """<div class="w-full {{ $isTech ? '' : 'sm:w-1/3' }} mb-8 sm:mb-0 print:mb-0 print:w-1/3">"""
if search8 in content:
    content = content.replace(search8, replace8)

search9 = """<div class="flex w-full sm:w-2/3 justify-around print:w-2/3">"""
replace9 = """<div class="flex w-full {{ $isTech ? '' : 'sm:w-2/3' }} justify-around print:w-2/3">"""
if search9 in content:
    content = content.replace(search9, replace9)

with open(file_blade, 'w', encoding='utf-8') as f:
    f.write(content)
print("Blade mobile layout logic updated")
