@extends('layouts.app')

@section('title', 'Vendor Management')

@section('content')
    <livewire:isp.vendors.index />
    <livewire:isp.vendors.form />
    <livewire:isp.vendors.detail />
@endsection
