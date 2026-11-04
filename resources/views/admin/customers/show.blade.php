@extends('layouts.app')

@section('title', 'Customer Details')
@section('eyebrow', 'Management / Customers / Details')
@section('page-title', 'Customer Details')

@section('content')

<div class="relay-page-actions">

    <p class="relay-section-description">
        View customer information, registered devices,
        and service history.
    </p>

    <div class="relay-row-actions">

        <a href="{{ route('admin.customers.index') }}"
           class="relay-action-link">
            Back
        </a>

        <a href="{{ route('admin.customers.edit', $customer) }}"
           class="relay-button">
            Edit Customer
        </a>

    </div>

</div>

<section class="relay-panel">

    <div class="relay-customer-identity"
         style="margin-bottom: 30px;">

        <div class="relay-customer-avatar"
             style="width: 54px; height: 54px; font-size: 20px;">
            {{ strtoupper(substr($customer->name, 0, 1)) }}
        </div>

        <div>
            <h2 class="relay-panel-title">
                {{ $customer->name }}
            </h2>

            <div class="relay-customer-id">
                Customer #{{ str_pad(
                    $customer->id,
                    4,
                    '0',
                    STR_PAD_LEFT
                ) }}
            </div>
        </div>

    </div>

    <div class="relay-detail-grid">

        <div>
            <div class="relay-detail-label">Phone Number</div>
            <div class="relay-detail-value">
                {{ $customer->phone }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Email Address</div>
            <div class="relay-detail-value">
                {{ $customer->email ?? 'Not provided' }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Address</div>
            <div class="relay-detail-value">
                {{ $customer->address ?? 'Not provided' }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Registered On</div>
            <div class="relay-detail-value">
                {{ $customer->created_at->format('d M Y') }}
            </div>
        </div>

    </div>

</section>

<section class="relay-panel relay-detail-section">

    <h2 class="relay-panel-title">
        Registered Devices
    </h2>

    <p class="relay-section-description">
        Devices associated with this customer.
    </p>

    <div class="relay-detail-list">

        @forelse ($customer->devices as $device)

            <div class="relay-detail-item">
                <strong>
                    {{ $device->brand }}
                    {{ $device->model }}
                </strong>

                <div class="relay-muted" style="margin-top: 6px;">
                    {{ $device->type }}
                </div>
            </div>

        @empty

            <div class="relay-empty">
                No registered devices yet.
            </div>

        @endforelse

    </div>

</section>

<section class="relay-panel relay-detail-section">

    <h2 class="relay-panel-title">
        Service History
    </h2>

    <p class="relay-section-description">
        Tickets associated with this customer.
    </p>

    <div class="relay-detail-list">

        @forelse ($customer->tickets as $ticket)

            <div class="relay-detail-item">
                <strong>
                    {{ $ticket->ticket_number }}
                </strong>

                <div style="margin-top: 6px;">
                    {{ $ticket->subject }}
                </div>

                <div class="relay-muted" style="margin-top: 6px;">
                    Status: {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                </div>
            </div>

        @empty

            <div class="relay-empty">
                No service tickets yet.
            </div>

        @endforelse

    </div>

</section>

@endsection
