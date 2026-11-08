@extends('layouts.app')

@section('title', $ticket->ticket_number)
@section('eyebrow', 'Operations / Service Orders / Details')
@section('page-title', 'Ticket Details')

@section('content')

@if (session('success'))
    <div class="relay-alert success" role="status">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="relay-alert error" role="alert">
        @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
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
            <div class="relay-approval-eyebrow">Service Authorization</div>
            <h2 class="relay-panel-title">Diagnosis & Customer Approval</h2>
            <p class="relay-section-description">Review the latest technical assessment and record the customer's decision.</p>
        </div>
    </div>

    @php $diagnosis = $ticket->diagnoses->first(); @endphp

    @if ($diagnosis)
        <div class="relay-approval-details">
            <div><span>Technician</span><strong>{{ $diagnosis->technician?->name ?? 'Unknown' }}</strong></div>
            <div><span>Estimated Cost</span><strong>Rp {{ number_format($diagnosis->estimated_cost, 0, ',', '.') }}</strong></div>
            <div><span>Approval Status</span><strong class="relay-approval-state {{ $diagnosis->approval_status }}">{{ ucfirst($diagnosis->approval_status) }}</strong></div>
            <div><span>Diagnosed At</span><strong>{{ $diagnosis->diagnosed_at?->format('d M Y, H:i') ?? '—' }}</strong></div>
        </div>
        <div class="relay-approval-text">
            <div><span>Findings</span><p>{{ $diagnosis->findings }}</p></div>
            <div><span>Recommended Action</span><p>{{ $diagnosis->recommended_action }}</p></div>
        </div>

        @if ($ticket->status === 'awaiting_approval' && $diagnosis->approval_status === 'pending' && (float) $diagnosis->estimated_cost > 0)
            <form method="POST" action="{{ route('admin.tickets.diagnosis.decide', $ticket) }}" class="relay-approval-form">
                @csrf
                @method('PATCH')
                <label for="decision_note">Customer Decision Notes <span aria-hidden="true">*</span></label>
                <textarea id="decision_note" name="decision_note" maxlength="2000" required placeholder="E.g. Customer confirmed the estimate by phone at 14:30.">{{ old('decision_note') }}</textarea>
                @error('decision_note') <p class="relay-approval-error">{{ $message }}</p> @enderror
                @error('decision') <p class="relay-approval-error">{{ $message }}</p> @enderror
                <div class="relay-approval-buttons">
                    <button type="submit" name="decision" value="approved" class="relay-approval-button approve" onclick="return confirm('Confirm that the customer approved this estimate?')">Approve Diagnosis</button>
                    <button type="submit" name="decision" value="rejected" class="relay-approval-button reject" onclick="return confirm('Confirm that the customer rejected this estimate?')">Reject Diagnosis</button>
                </div>
            </form>
        @elseif ($diagnosis->approval_status === 'approved')
            <div class="relay-approval-notice">Approved. The assigned technician can now move this ticket to Repairing.</div>
        @elseif ($diagnosis->approval_status === 'rejected')
            <div class="relay-approval-notice">Rejected. This ticket is On Hold and cannot proceed to repair. Admin may cancel it if needed.</div>
        @else
            <div class="relay-approval-notice">Approval actions become available when a paid diagnosis is awaiting approval.</div>
        @endif
    @else
        <div class="relay-approval-notice">No diagnosis has been recorded for this ticket yet.</div>
    @endif
</section>

<section class="relay-panel relay-ticket-section">

    <h2 class="relay-panel-title">Activity Timeline</h2>

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