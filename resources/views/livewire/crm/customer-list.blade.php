@extends('layouts.admin')

@section('title', 'Customers - WiFiNan')

@section('breadcrumb')
<span class="text-slate-500">CRM</span>
<span class="text-slate-400 mx-2">/</span>
<span class="text-slate-900">Customers</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Customers</h1>
            <p class="mt-1 text-sm text-slate-500">Manage your customers and their services</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('crm.customers.export') }}" class="btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export
            </a>
            <a href="{{ route('crm.customers.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Customer
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card">
            <div class="card-body flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                    <p class="text-sm text-slate-500">Total Customers</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-success-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-success-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['active'] }}</p>
                    <p class="text-sm text-slate-500">Active</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-warning-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-warning-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['inactive'] }}</p>
                    <p class="text-sm text-slate-500">Inactive</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-secondary-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-secondary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['new_this_month'] }}</p>
                    <p class="text-sm text-slate-500">New This Month</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <div class="flex flex-wrap items-center gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="filters.search" placeholder="Search customers..." class="input pl-10">
                    </div>
                </div>

                <!-- Status Filter -->
                <select wire:model.live="filters.status" class="select w-auto">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>

                <!-- Package Filter -->
                <select wire:model.live="filters.package" class="select w-auto">
                    <option value="all">All Packages</option>
                    <option value="basic">Basic 50 Mbps</option>
                    <option value="standard">Standard 75 Mbps</option>
                    <option value="premium">Premium 100 Mbps</option>
                </select>

                <!-- Area Filter -->
                <select wire:model.live="filters.area" class="select w-auto">
                    <option value="all">All Areas</option>
                    <option value="jakarta_selatan">Jakarta Selatan</option>
                    <option value="jakarta_barat">Jakarta Barat</option>
                    <option value="jakarta_timur">Jakarta Timur</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Customer Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-10">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300">
                        </th>
                        <th class="cursor-pointer" wire:click="sortBy('name')">
                            <div class="flex items-center gap-2">
                                Customer
                                @if($sortField === 'name')
                                <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    @if($sortDirection === 'asc')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    @endif
                                </svg>
                                @endif
                            </div>
                        </th>
                        <th>Package</th>
                        <th>Area</th>
                        <th>Status</th>
                        <th class="cursor-pointer" wire:click="sortBy('created_at')">
                            <div class="flex items-center gap-2">
                                Created
                                @if($sortField === 'created_at')
                                <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    @if($sortDirection === 'asc')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    @endif
                                </svg>
                                @endif
                            </div>
                        </th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    <tr>
                        <td>
                            <input type="checkbox" value="{{ $customer['id'] }}" wire:model.live="selected" class="rounded border-slate-300">
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 flex items-center justify-center text-white font-semibold">
                                    {{ substr($customer['name'], 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900">{{ $customer['name'] }}</p>
                                    <p class="text-sm text-slate-500">{{ $customer['email'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-slate-700">{{ $customer['package'] }}</span>
                        </td>
                        <td>
                            <span class="text-slate-700">{{ $customer['area'] }}</span>
                        </td>
                        <td>
                            @if($customer['status'] === 'active')
                            <span class="badge badge-success">Active</span>
                            @elseif($customer['status'] === 'inactive')
                            <span class="badge badge-neutral">Inactive</span>
                            @else
                            <span class="badge badge-warning">Suspended</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-sm text-slate-500">{{ $customer['created_at']->diffForHumans() }}</span>
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('crm.customers.view', $customer['id']) }}" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <a href="{{ route('crm.customers.edit', $customer['id']) }}" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <button class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="card-footer flex items-center justify-between">
            <p class="text-sm text-slate-500">
                Showing {{ $customers->count() }} of {{ $stats['total'] }} customers
            </p>
            <div class="pagination">
                <button class="pagination-item">Previous</button>
                <button class="pagination-item pagination-item-active">1</button>
                <button class="pagination-item">2</button>
                <button class="pagination-item">3</button>
                <button class="pagination-item">Next</button>
            </div>
        </div>
    </div>
</div>
@endsection
