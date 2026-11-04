@extends('layouts.app')

@section('title', 'Devices')
@section('eyebrow', 'Management / Devices')
@section('page-title', 'Devices')

@section('content')

@if (session('success'))
    <div class="relay-alert success" role="status">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="relay-alert error" role="alert">
        {{ session('error') }}
    </div>
@endif

<div class="relay-page-actions">
    <p class="relay-section-description">
        Manage registered devices and their customer ownership.
    </p>

    <a href="{{ route('admin.devices.create') }}"
       class="relay-button">
        + Add Device
    </a>
</div>

<div class="relay-device-summary">

    <div class="relay-device-stat">
        <div class="relay-device-stat-label">
            Total Devices
        </div>
        <div class="relay-device-stat-value">
            {{ \App\Models\Device::count() }}
        </div>
    </div>

    <div class="relay-device-stat">
        <div class="relay-device-stat-label">
            Registered Customers
        </div>
        <div class="relay-device-stat-value">
            {{ \App\Models\Customer::has('devices')->count() }}
        </div>
    </div>

    <div class="relay-device-stat">
        <div class="relay-device-stat-label">
            Devices with Tickets
        </div>
        <div class="relay-device-stat-value">
            {{ \App\Models\Device::has('tickets')->count() }}
        </div>
    </div>

</div>

<section class="relay-panel">

    <h2 class="relay-panel-title">
        Device Inventory
    </h2>

    <p class="relay-section-description">
        Search and manage all registered customer devices.
    </p>

    <div class="relay-customer-toolbar">

        <form method="GET"
              action="{{ route('admin.devices.index') }}"
              class="relay-search-form">

            <input
                type="search"
                name="search"
                class="relay-input"
                value="{{ $search }}"
                placeholder="Search device, serial number, customer..."
                aria-label="Search devices"
            >

            <button type="submit"
                    class="relay-button relay-button-secondary">
                Search
            </button>

        </form>

        <span class="relay-customer-count">
            {{ $devices->total() }} devices
        </span>

    </div>

    <div class="relay-table-wrapper">
        <table class="relay-table">

            <thead>
                <tr>
                    <th>Device</th>
                    <th>Owner</th>
                    <th>Type</th>
                    <th>Tickets</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($devices as $device)
                    <tr>
                        <td>
                            <div class="relay-device-identity">

                                <div class="relay-device-icon">
                                    <svg viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.8"
                                         stroke-linecap="round"
                                         stroke-linejoin="round">
                                        <rect x="3" y="4"
                                              width="18" height="13"
                                              rx="2"/>
                                        <path d="M8 21h8M12 17v4"/>
                                    </svg>
                                </div>

                                <div>
                                    <div class="relay-device-name">
                                        {{ $device->brand }}
                                        {{ $device->model }}
                                    </div>

                                    <div class="relay-device-meta">
                                        SN: {{ $device->serial_number ?: 'Not provided' }}
                                    </div>
                                </div>

                            </div>
                        </td>

                        <td>
                            <div class="relay-device-owner">
                                {{ $device->customer->name }}
                            </div>

                            <div class="relay-device-owner-sub">
                                {{ $device->customer->phone }}
                            </div>
                        </td>

                        <td>
                            <span class="relay-device-badge">
                                {{ $device->type }}
                            </span>
                        </td>

                        <td>
                            {{ $device->tickets_count }}
                        </td>

                        <td>
                            <div class="relay-row-actions">

                                <a
                                    href="{{ route('admin.devices.show', $device) }}"
                                    class="relay-table-action">
                                    View
                                </a>

                                <a
                                    href="{{ route('admin.devices.edit', $device) }}"
                                    class="relay-table-action">
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.devices.destroy', $device) }}"
                                    onsubmit="return confirm('Delete this device?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="relay-table-action danger">
                                        Delete
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="relay-empty">
                                No devices found.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    @if ($devices->hasPages())
        <div class="relay-pagination">
            {{ $devices->links() }}
        </div>
    @endif

</section>

@endsection
