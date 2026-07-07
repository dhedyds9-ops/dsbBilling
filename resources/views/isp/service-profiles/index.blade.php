@extends('layouts.app')

@section('title', 'Service Profile Management')

@section('content')
    <!-- Livewire Components -->
    <livewire:isp.service-profiles.index />
    <livewire:isp.service-profiles.form />
    <livewire:isp.service-profiles.detail />
@endsection
