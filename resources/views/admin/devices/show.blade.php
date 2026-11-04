@extends('layouts.app')

@section('title', 'Device Details')
@section('eyebrow', 'Management / Devices / Details')
@section('page-title', 'Device Details')

@section('content')

<div class="relay-page-actions">

    <p class="relay-section-description">
        Device information, customer ownership,
        and service history.
    </p>

    <div class="relay-row-actions">

        <a href="{{ route('admin.devices.index') }}"
           class="relay-action-link">
            Back
        </a>

        <a href="{{ route('admin.devices.edit', $device) }}"
           class="relay-button">
            Edit Device
        </a>

    </div>

</div>

<section class="relay-panel">

    <div class="relay-device-detail-header">

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
            <h2 class="relay-device-detail-title">
                {{ $device->brand }}
                {{ $device->model }}
            </h2>

            <div class="relay-device-detail-subtitle">
                Device #{{ str_pad($device->id, 4, '0', STR_PAD_LEFT) }}
            </div>
        </div>

    </div>

    <div class="relay-detail-grid">

        <div>
            <div class="relay-detail-label">Device Type</div>
            <div class="relay-detail-value">
                {{ $device->type }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Brand</div>
            <div class="relay-detail-value">
                {{ $device->brand }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Model</div>
            <div class="relay-detail-value">
                {{ $device->model ?: 'Not provided' }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Serial Number</div>
            <div class="relay-detail-value">
                {{ $device->serial_number ?: 'Not provided' }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Registered On</div>
            <div class="relay-detail-value">
                {{ $device->created_at->format('d M Y') }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Customer</div>
            <div class="relay-detail-value">
                <a
                    href="{{ route('admin.customers.show', $device->customer) }}"
                    class="relay-device-link">
                    {{ $device->customer->name }}
                </a>
            </div>
        </div>

        <div style="grid-column: 1 / -1;">
            <div class="relay-detail-label">Additional Notes</div>
            <div class="relay-detail-value"
                 style="white-space: pre-wrap;">{{ $device->notes ?: 'No additional notes.' }}</div>
        </div>

    </div>

</section>

<section class="relay-panel relay-detail-section">

    <h2 class="relay-panel-title">
        Service History
    </h2>

    <p class="relay-section-description">
        All service tickets associated with this device.
    </p>

    <div class="relay-detail-list">

        @forelse ($device->tickets as $ticket)

            <div class="relay-detail-item">

                <strong>
                    {{ $ticket->ticket_number }}
                </strong>

                <div style="margin-top: 6px;">
                    {{ $ticket->subject }}
                </div>

                <div class="relay-muted" style="margin-top: 6px;">
                    Status:
                    {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                </div>

            </div>

        @empty

            <div class="relay-empty">
                No service tickets for this device yet.
            </div>

        @endforelse

    </div>

</section>

@endsection
