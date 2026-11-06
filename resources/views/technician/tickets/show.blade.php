@extends('layouts.app')

@section('title', $ticket->ticket_number)
@section('eyebrow', 'Workspace / Assigned Tickets / Details')
@section('page-title', 'Ticket Details')

@section('content')

<div class="relay-page-actions">

    <div>
        <div class="relay-ticket-number">
            {{ $ticket->ticket_number }}
        </div>

        <p class="relay-section-description">
            {{ $ticket->subject }}
        </p>
    </div>

    <a href="{{ route('technician.tickets.index') }}"
       class="relay-action-link">
        Back to Tickets
    </a>

</div>

<section class="relay-panel">

    <div class="relay-row-actions"
         style="margin-bottom: 28px;">

        <span class="relay-ticket-badge {{ $ticket->status }}">
            {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
        </span>

        <span class="relay-ticket-badge {{ $ticket->priority }}">
            {{ ucfirst($ticket->priority) }} Priority
        </span>

    </div>

    <div class="relay-tech-info-grid">

        <div>
            <div class="relay-detail-label">Customer</div>
            <div class="relay-detail-value">
                {{ $ticket->customer->name }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Customer Phone</div>
            <div class="relay-detail-value">
                {{ $ticket->customer->phone }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Device</div>
            <div class="relay-detail-value">
                {{ $ticket->device->brand }}
                {{ $ticket->device->model }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Serial Number</div>
            <div class="relay-detail-value">
                {{ $ticket->device->serial_number ?: 'Not provided' }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Category</div>
            <div class="relay-detail-value">
                {{ $ticket->category->name }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Received At</div>
            <div class="relay-detail-value">
                {{ $ticket->received_at?->format('d M Y, H:i') ?? '—' }}
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

<section class="relay-panel relay-tech-section">

    <h2 class="relay-panel-title">
        Activity Timeline
    </h2>

    <p class="relay-section-description">
        Recorded service ticket history.
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

            <div class="relay-tech-empty">
                No activity recorded yet.
            </div>

        @endforelse

    </div>

</section>

<section class="relay-panel relay-tech-section">

    <h2 class="relay-panel-title">
        Technician Actions
    </h2>

    <p class="relay-section-description">
        Diagnosis, internal notes, and service status updates
        will be available in the next implementation step.
    </p>

    <div class="relay-ticket-warning"
         style="margin-top: 20px;">
        This ticket is currently in read-only mode.
        Technician actions will be enabled in Step 5.2.
    </div>

</section>

@endsection
