@extends('layouts.app')

@section('title', 'Data Vendor')

@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">storefront</span>
    <span class="text-lg">Data Vendor</span>
@endsection

@section('content')
    <livewire:isp.vendors.index />
    <livewire:isp.vendors.form />
    <livewire:isp.vendors.detail />
@endsection
