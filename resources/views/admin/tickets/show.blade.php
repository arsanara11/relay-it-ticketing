@extends('layouts.app')

@section('title', $ticket->ticket_number)
@section('eyebrow', 'Operations / Service Orders / Details')
@section('page-title', 'Ticket Details')

@section('content')

@vite('resources/css/billing.css')

@if (session('success'))
    <div class="relay-alert success" role="status">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="relay-alert error" role="alert">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

@if (session('error'))
    <div class="relay-alert error" role="alert">
        {{ session('error') }}
    </div>
@endif

<div class="relay-page-actions">
    <div>
        <div class="relay-ticket-number">
            {{ $ticket->ticket_number }}
        </div>

        <p class="relay-section-description">
            {{ $ticket->subject }}
        </p>
    </div>

    <div class="relay-row-actions">
        <a href="{{ route('admin.tickets.index') }}"
           class="relay-action-link">
            Back
        </a>

        @unless (in_array($ticket->status, ['completed', 'cancelled']))
            <a href="{{ route('admin.tickets.edit', $ticket) }}"
               class="relay-button">
                Edit Ticket
            </a>

            <form
                method="POST"
                action="{{ route('admin.tickets.destroy', $ticket) }}"
                onsubmit="return confirm('Cancel this service ticket? This action cannot be undone.')"
            >
                @csrf
                @method('DELETE')

                <button type="submit"
                        class="relay-table-action danger">
                    Cancel Ticket
                </button>
            </form>
        @endunless
    </div>
</div>

<section class="relay-panel">
    <div class="relay-row-actions" style="margin-bottom: 28px;">
        <span class="relay-ticket-badge {{ $ticket->status }}">
            {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
        </span>

        <span class="relay-ticket-badge {{ $ticket->priority }}">
            {{ ucfirst($ticket->priority) }} Priority
        </span>
    </div>

    <div class="relay-ticket-detail-grid">
        <div>
            <div class="relay-detail-label">Customer</div>
            <div class="relay-detail-value">
                <a href="{{ route('admin.customers.show', $ticket->customer) }}"
                   class="relay-device-link">
                    {{ $ticket->customer->name }}
                </a>
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Device</div>
            <div class="relay-detail-value">
                <a href="{{ route('admin.devices.show', $ticket->device) }}"
                   class="relay-device-link">
                    {{ $ticket->device->brand }}
                    {{ $ticket->device->model }}
                </a>
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Service Category</div>
            <div class="relay-detail-value">
                {{ $ticket->category->name }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Assigned Technician</div>
            <div class="relay-detail-value">
                {{ $ticket->technician?->name ?? 'Unassigned' }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Received At</div>
            <div class="relay-detail-value">
                {{ $ticket->received_at?->format('d M Y, H:i') ?? '—' }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Created By</div>
            <div class="relay-detail-value">
                {{ $ticket->creator->name }}
            </div>
        </div>

        <div style="grid-column: 1 / -1;">
            <div class="relay-detail-label">Issue Description</div>
            <div class="relay-detail-value"
                 style="white-space: pre-wrap;">{{ $ticket->description }}</div>
        </div>

        <div style="grid-column: 1 / -1;">
            <div class="relay-detail-label">Initial Condition</div>
            <div class="relay-detail-value"
                 style="white-space: pre-wrap;">{{ $ticket->initial_condition ?: 'Not provided' }}</div>
        </div>

        <div style="grid-column: 1 / -1;">
            <div class="relay-detail-label">Included Accessories</div>
            <div class="relay-detail-value"
                 style="white-space: pre-wrap;">{{ $ticket->accessories ?: 'Not provided' }}</div>
        </div>
    </div>
</section>

<section class="relay-panel relay-ticket-section">
    <div class="relay-approval-heading">
        <div>
            <div class="relay-approval-eyebrow">
                Service Authorization
            </div>

            <h2 class="relay-panel-title">
                Diagnosis & Customer Approval
            </h2>

            <p class="relay-section-description">
                Review the latest technical assessment and record the customer's decision.
            </p>
        </div>
    </div>

    @php
        $diagnosis = $ticket->diagnoses->first();
    @endphp

    @if ($diagnosis)
        <div class="relay-approval-details">
            <div>
                <span>Technician</span>
                <strong>
                    {{ $diagnosis->technician?->name ?? 'Unknown' }}
                </strong>
            </div>

            <div>
                <span>Estimated Cost</span>
                <strong>
                    Rp {{ number_format($diagnosis->estimated_cost, 0, ',', '.') }}
                </strong>
            </div>

            <div>
                <span>Approval Status</span>
                <strong class="relay-approval-state {{ $diagnosis->approval_status }}">
                    {{ ucfirst($diagnosis->approval_status) }}
                </strong>
            </div>

            <div>
                <span>Diagnosed At</span>
                <strong>
                    {{ $diagnosis->diagnosed_at?->format('d M Y, H:i') ?? '—' }}
                </strong>
            </div>
        </div>

        <div class="relay-approval-text">
            <div>
                <span>Findings</span>
                <p>{{ $diagnosis->findings }}</p>
            </div>

            <div>
                <span>Recommended Action</span>
                <p>{{ $diagnosis->recommended_action }}</p>
            </div>
        </div>

        @if (
            $ticket->status === 'awaiting_approval'
            && $diagnosis->approval_status === 'pending'
            && (float) $diagnosis->estimated_cost > 0
        )
            <form
                method="POST"
                action="{{ route('admin.tickets.diagnosis.decide', $ticket) }}"
                class="relay-approval-form"
            >
                @csrf
                @method('PATCH')

                <label for="decision_note">
                    Customer Decision Notes
                    <span aria-hidden="true">*</span>
                </label>

                <textarea
                    id="decision_note"
                    name="decision_note"
                    maxlength="2000"
                    required
                    placeholder="E.g. Customer confirmed the estimate by phone at 14:30."
                >{{ old('decision_note') }}</textarea>

                @error('decision_note')
                    <p class="relay-approval-error">{{ $message }}</p>
                @enderror

                @error('decision')
                    <p class="relay-approval-error">{{ $message }}</p>
                @enderror

                <div class="relay-approval-buttons">
                    <button
                        type="submit"
                        name="decision"
                        value="approved"
                        class="relay-approval-button approve"
                        onclick="return confirm('Confirm that the customer approved this estimate?')"
                    >
                        Approve Diagnosis
                    </button>

                    <button
                        type="submit"
                        name="decision"
                        value="rejected"
                        class="relay-approval-button reject"
                        onclick="return confirm('Confirm that the customer rejected this estimate?')"
                    >
                        Reject Diagnosis
                    </button>
                </div>
            </form>
        @elseif ($diagnosis->approval_status === 'approved')
            <div class="relay-approval-notice">
                Approved. The assigned technician can now move this ticket to Repairing.
            </div>
        @elseif ($diagnosis->approval_status === 'rejected')
            <div class="relay-approval-notice">
                Rejected. This ticket is On Hold and cannot proceed to repair.
                Admin may cancel it if needed.
            </div>
        @else
            <div class="relay-approval-notice">
                Approval actions become available when a paid diagnosis is awaiting approval.
            </div>
        @endif
    @else
        <div class="relay-approval-notice">
            No diagnosis has been recorded for this ticket yet.
        </div>
    @endif
</section>

@php
    $billingTotalCents = $ticket->costs->sum(fn ($cost) =>
        (int) $cost->quantity
        * (int) round((float) $cost->unit_price * 100)
    );

    $billingPaidCents = $ticket->payments->sum(fn ($payment) =>
        (int) round((float) $payment->amount * 100)
    );

    $billingBalanceCents = max(
        0,
        $billingTotalCents - $billingPaidCents
    );

    $billingReady = $ticket->status === 'ready_for_pickup'
        && ! $ticket->handover;

    $billingCostsEditable = $billingReady
        && $ticket->payments->isEmpty();
@endphp

<section class="relay-panel relay-ticket-section relay-billing">
    <div class="relay-billing-heading">
        <div>
            <div class="relay-billing-eyebrow">
                Service Settlement
            </div>

            <h2 class="relay-panel-title">
                Service Costs & Payments
            </h2>

            <p class="relay-section-description">
                Manage charges, payments, and the outstanding balance.
            </p>
        </div>

        <span class="relay-ticket-badge {{ $ticket->status }}">
            {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
        </span>
    </div>

    <div class="relay-billing-stats">
        <div>
            <span>Total Charges</span>
            <strong>
                Rp {{ number_format($billingTotalCents / 100, 0, ',', '.') }}
            </strong>
        </div>

        <div>
            <span>Total Paid</span>
            <strong>
                Rp {{ number_format($billingPaidCents / 100, 0, ',', '.') }}
            </strong>
        </div>

        <div>
            <span>Balance Due</span>
            <strong>
                Rp {{ number_format($billingBalanceCents / 100, 0, ',', '.') }}
            </strong>
        </div>
    </div>

    @if (! $billingReady)
        <p class="relay-billing-notice">
            Financial changes are available when a ticket is Ready for Pickup.
            Completed and cancelled tickets are read-only.
        </p>
    @elseif (! $billingCostsEditable)
        <p class="relay-billing-notice">
            Charges are locked because a payment has already been recorded.
        </p>
    @endif

    <h3 class="relay-billing-subtitle">
        Cost Breakdown
    </h3>

    <div class="relay-billing-scroll">
        <table class="relay-billing-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @forelse ($ticket->costs as $cost)
                    <tr>
                        <td>{{ $cost->description }}</td>
                        <td>{{ ucfirst($cost->type) }}</td>
                        <td>{{ $cost->quantity }}</td>
                        <td>
                            Rp {{ number_format($cost->unit_price, 0, ',', '.') }}
                        </td>
                        <td>
                            Rp {{ number_format($cost->quantity * $cost->unit_price, 0, ',', '.') }}
                        </td>
                        <td>
                            @if ($billingCostsEditable)
                                <form
                                    method="POST"
                                    action="{{ route('admin.tickets.costs.destroy', [$ticket, $cost]) }}"
                                    onsubmit="return confirm('Remove this cost item?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="relay-billing-danger"
                                    >
                                        Remove
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="relay-billing-empty">
                            No service costs recorded.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($billingCostsEditable)
        <form
            method="POST"
            action="{{ route('admin.tickets.costs.store', $ticket) }}"
            class="relay-billing-form"
        >
            @csrf

            <div class="relay-billing-field relay-billing-wide">
                <label for="cost-description">Description</label>
                <input
                    id="cost-description"
                    name="description"
                    value="{{ old('description') }}"
                    maxlength="255"
                    required
                    placeholder="e.g. SSD replacement"
                >
            </div>

            <div class="relay-billing-field">
                <label for="cost-type">Type</label>
                <select id="cost-type" name="type" required>
                    <option value="service">Service</option>
                    <option value="part">Part</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="relay-billing-field">
                <label for="cost-quantity">Quantity</label>
                <input
                    id="cost-quantity"
                    type="number"
                    name="quantity"
                    min="1"
                    max="10000"
                    value="{{ old('quantity', 1) }}"
                    required
                >
            </div>

            <div class="relay-billing-field">
                <label for="cost-unit-price">
                    Unit Price (IDR)
                </label>
                <input
                    id="cost-unit-price"
                    type="number"
                    name="unit_price"
                    min="0"
                    max="999999999.99"
                    step="0.01"
                    value="{{ old('unit_price', 0) }}"
                    required
                >
            </div>

            <div class="relay-billing-form-actions">
                <button type="submit" class="relay-billing-button">
                    Add Cost
                </button>
            </div>
        </form>
    @endif

    <h3 class="relay-billing-subtitle">
        Payment History
    </h3>

    <div class="relay-billing-scroll">
        <table class="relay-billing-table">
            <thead>
                <tr>
                    <th>Paid At</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Reference</th>
                    <th>Recorded By</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($ticket->payments as $payment)
                    <tr>
                        <td>
                            {{ $payment->paid_at?->format('d M Y, H:i') ?? '—' }}
                        </td>
                        <td>
                            {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                        </td>
                        <td>
                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                        </td>
                        <td>
                            {{ $payment->reference_number ?: '—' }}
                        </td>
                        <td>
                            {{ $payment->recorder?->name ?? 'Unknown' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="relay-billing-empty">
                            No payments recorded.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($billingReady && $billingBalanceCents > 0)
        <form
            method="POST"
            action="{{ route('admin.tickets.payments.store', $ticket) }}"
            class="relay-billing-form"
        >
            @csrf

            <div class="relay-billing-field">
                <label for="payment-amount">
                    Amount (IDR)
                </label>
                <input
                    id="payment-amount"
                    type="number"
                    name="amount"
                    min="0.01"
                    max="{{ number_format($billingBalanceCents / 100, 2, '.', '') }}"
                    step="0.01"
                    required
                >
            </div>

            <div class="relay-billing-field">
                <label for="payment-method">
                    Payment Method
                </label>
                <select id="payment-method" name="payment_method" required>
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="qris">QRIS</option>
                    <option value="debit_card">Debit Card</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="relay-billing-field">
                <label for="payment-reference">
                    Reference Number
                </label>
                <input
                    id="payment-reference"
                    name="reference_number"
                    maxlength="255"
                    placeholder="Optional"
                >
            </div>

            <div class="relay-billing-field relay-billing-wide">
                <label for="payment-notes">
                    Payment Notes
                </label>
                <textarea
                    id="payment-notes"
                    name="notes"
                    maxlength="2000"
                    placeholder="Optional"
                ></textarea>
            </div>

            <div class="relay-billing-form-actions">
                <button type="submit" class="relay-billing-button">
                    Record Payment
                </button>
            </div>
        </form>
    @endif
</section>

<section class="relay-panel relay-ticket-section relay-billing">
    <div class="relay-billing-eyebrow">
        Final Step
    </div>

    <h2 class="relay-panel-title">
        Device Handover
    </h2>

    <p class="relay-section-description">
        Complete the service after confirming payment and device collection.
    </p>

    @if ($ticket->handover)
        <div class="relay-billing-handover">
            <div>
                <span>Received By</span>
                <strong>
                    {{ $ticket->handover->received_by }}
                </strong>
            </div>

            <div>
                <span>Handed Over By</span>
                <strong>
                    {{ $ticket->handover->staff?->name ?? 'Unknown' }}
                </strong>
            </div>

            <div>
                <span>Handed Over At</span>
                <strong>
                    {{ $ticket->handover->handed_over_at?->format('d M Y, H:i') ?? '—' }}
                </strong>
            </div>

            <div class="relay-billing-wide">
                <span>Notes</span>
                <strong>
                    {{ $ticket->handover->notes ?: '—' }}
                </strong>
            </div>
        </div>
    @elseif ($billingReady && $billingBalanceCents === 0)
        <form
            method="POST"
            action="{{ route('admin.tickets.handover.store', $ticket) }}"
            class="relay-billing-form"
            onsubmit="return confirm('Confirm device handover and complete this ticket?')"
        >
            @csrf

            <div class="relay-billing-field">
                <label for="received-by">
                    Received By
                </label>
                <input
                    id="received-by"
                    name="received_by"
                    maxlength="255"
                    value="{{ old('received_by', $ticket->customer->name) }}"
                    required
                >
            </div>

            <div class="relay-billing-field relay-billing-wide">
                <label for="handover-notes">
                    Handover Notes
                </label>
                <textarea
                    id="handover-notes"
                    name="notes"
                    maxlength="2000"
                    placeholder="Condition and accessories returned"
                >{{ old('notes') }}</textarea>
            </div>

            <div class="relay-billing-form-actions">
                <button type="submit" class="relay-billing-button">
                    Confirm Handover & Complete
                </button>
            </div>
        </form>
    @else
        <p class="relay-billing-notice">
            Handover is available only when the ticket is Ready for Pickup
            and the outstanding balance is zero.
        </p>
    @endif
</section>

<section class="relay-panel relay-ticket-section">
    <h2 class="relay-panel-title">
        Activity Timeline
    </h2>

    <p class="relay-section-description">
        Recorded changes and actions for this service ticket.
    </p>

    <div class="relay-ticket-activity">
        @forelse ($ticket->activities as $activity)
            <div class="relay-ticket-activity-item">
                <div class="relay-ticket-activity-title">
                    {{ ucwords(str_replace('_', ' ', $activity->action)) }}
                </div>

                <div class="relay-ticket-activity-meta">
                    {{ $activity->user?->name ?? 'System' }}
                    ·
                    {{ $activity->created_at->format('d M Y, H:i') }}
                </div>

                <div class="relay-ticket-activity-description">
                    {{ $activity->description }}
                </div>

                @if ($activity->old_status !== $activity->new_status)
                    <div class="relay-ticket-activity-meta">
                        {{ $activity->old_status ?: 'New' }}
                        →
                        {{ $activity->new_status }}
                    </div>
                @endif
            </div>
        @empty
            <div class="relay-empty">
                No activity recorded yet.
            </div>
        @endforelse
    </div>
</section>

@endsection
