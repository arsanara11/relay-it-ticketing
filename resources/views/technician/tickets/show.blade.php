@extends('layouts.app')

@section('title', $ticket->ticket_number)
@section('eyebrow', 'Workspace / Assigned Tickets / Details')
@section('page-title', 'Ticket Details')

@section('content')

@php
    $latestDiagnosis = $ticket->diagnoses->first();

    $isClosed = in_array(
        $ticket->status,
        ['completed', 'cancelled'],
        true
    );

    $canEditDiagnosis = in_array(
        $ticket->status,
        ['received', 'diagnosing'],
        true
    ) && (
        ! $latestDiagnosis
        || $latestDiagnosis->approval_status === 'pending'
    );

    $statusLabels = [
        'received' => 'Received',
        'diagnosing' => 'Diagnosing',
        'awaiting_approval' => 'Awaiting Approval',
        'repairing' => 'Repairing',
        'ready_for_pickup' => 'Ready for Pickup',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'on_hold' => 'On Hold',
    ];
@endphp

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

@if (session('success'))
    <div class="relay-tech-alert relay-tech-alert-success"
         role="status">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="relay-tech-alert relay-tech-alert-error"
         role="alert">
        <strong>Please review the following:</strong>

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="relay-panel">
    <div class="relay-row-actions"
         style="margin-bottom: 28px;">
        <span class="relay-ticket-badge {{ $ticket->status }}">
            {{ $statusLabels[$ticket->status] ?? $ticket->status }}
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

        <div class="relay-tech-full-width">
            <div class="relay-detail-label">Issue Description</div>
            <div class="relay-detail-value relay-tech-prewrap">{{ $ticket->description }}</div>
        </div>

        <div class="relay-tech-full-width">
            <div class="relay-detail-label">Initial Condition</div>
            <div class="relay-detail-value relay-tech-prewrap">{{ $ticket->initial_condition ?: 'Not provided' }}</div>
        </div>

        <div class="relay-tech-full-width">
            <div class="relay-detail-label">Included Accessories</div>
            <div class="relay-detail-value relay-tech-prewrap">{{ $ticket->accessories ?: 'Not provided' }}</div>
        </div>
    </div>
</section>

<div class="relay-tech-workspace">

    <div class="relay-tech-workspace-main">

        <section class="relay-panel relay-tech-section">
            <div class="relay-tech-panel-heading">
                <div>
                    <div class="relay-tech-overline">Service Workflow</div>
                    <h2 class="relay-panel-title">Update Ticket Status</h2>
                    <p class="relay-section-description">
                        Move this ticket to the next valid service stage.
                    </p>
                </div>
            </div>

            <div class="relay-tech-current-status">
                <span class="relay-tech-muted">Current status</span>
                <span class="relay-ticket-badge {{ $ticket->status }}">
                    {{ $statusLabels[$ticket->status] ?? $ticket->status }}
                </span>
            </div>

            @if (count($availableStatuses) > 0)
                <form method="POST"
                      action="{{ route('technician.tickets.status.update', $ticket) }}"
                      class="relay-tech-form">
                    @csrf
                    @method('PATCH')

                    <div class="relay-tech-field">
                        <label for="ticket-status"
                               class="relay-tech-label">
                            Next Status
                        </label>

                        <select id="ticket-status"
                                name="status"
                                class="relay-tech-input"
                                required>
                            <option value="">Select next status</option>

                            @foreach ($availableStatuses as $nextStatus)
                                <option value="{{ $nextStatus }}"
                                    @selected(old('status') === $nextStatus)>
                                    {{ $statusLabels[$nextStatus] ?? $nextStatus }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                            class="relay-tech-submit">
                        Update Status
                    </button>
                </form>

                <p class="relay-tech-hint">
                    A diagnosis is required before repair.
                    Paid repairs also require approval.
                </p>
            @else
                <div class="relay-tech-notice">
                    No technician status changes are available
                    at this stage.
                </div>
            @endif
        </section>

        <section class="relay-panel relay-tech-section">
            <div class="relay-tech-panel-heading">
                <div>
                    <div class="relay-tech-overline">Technical Assessment</div>
                    <h2 class="relay-panel-title">Device Diagnosis</h2>
                    <p class="relay-section-description">
                        Document the issue, proposed solution,
                        and estimated repair cost.
                    </p>
                </div>
            </div>

            @if ($latestDiagnosis)
                <div class="relay-tech-diagnosis-summary">
                    <div class="relay-tech-summary-row">
                        <span class="relay-tech-muted">Approval</span>
                        <span class="relay-tech-approval {{ $latestDiagnosis->approval_status }}">
                            {{ ucfirst($latestDiagnosis->approval_status) }}
                        </span>
                    </div>

                    <div class="relay-tech-summary-row">
                        <span class="relay-tech-muted">Estimated Cost</span>
                        <strong>
                            Rp {{ number_format($latestDiagnosis->estimated_cost, 0, ',', '.') }}
                        </strong>
                    </div>

                    <div class="relay-tech-summary-row">
                        <span class="relay-tech-muted">Last Diagnosed</span>
                        <span>
                            {{ $latestDiagnosis->diagnosed_at?->format('d M Y, H:i') ?? '—' }}
                        </span>
                    </div>
                </div>
            @endif

            @if ($canEditDiagnosis)
                <form method="POST"
                      action="{{ route('technician.tickets.diagnosis.store', $ticket) }}"
                      class="relay-tech-form">
                    @csrf

                    <div class="relay-tech-field">
                        <label for="findings"
                               class="relay-tech-label">
                            Findings
                        </label>

                        <textarea id="findings"
                                  name="findings"
                                  class="relay-tech-input relay-tech-textarea"
                                  maxlength="5000"
                                  placeholder="Describe the issue found during inspection..."
                                  required>{{ old('findings', $latestDiagnosis?->findings) }}</textarea>
                    </div>

                    <div class="relay-tech-field">
                        <label for="recommended-action"
                               class="relay-tech-label">
                            Recommended Action
                        </label>

                        <textarea id="recommended-action"
                                  name="recommended_action"
                                  class="relay-tech-input relay-tech-textarea"
                                  maxlength="5000"
                                  placeholder="Describe the recommended repair..."
                                  required>{{ old('recommended_action', $latestDiagnosis?->recommended_action) }}</textarea>
                    </div>

                    <div class="relay-tech-field">
                        <label for="estimated-cost"
                               class="relay-tech-label">
                            Estimated Cost (IDR)
                        </label>

                        <input id="estimated-cost"
                               type="number"
                               name="estimated_cost"
                               class="relay-tech-input"
                               min="0"
                               max="999999999.99"
                               step="0.01"
                               value="{{ old('estimated_cost', $latestDiagnosis?->estimated_cost ?? 0) }}"
                               required>

                        <span class="relay-tech-hint">
                            Enter 0 for a repair that requires no charge.
                        </span>
                    </div>

                    <button type="submit"
                            class="relay-tech-submit">
                        {{ $latestDiagnosis ? 'Update Diagnosis' : 'Save Diagnosis' }}
                    </button>
                </form>
            @elseif ($latestDiagnosis)
                <div class="relay-tech-readonly">
                    <div class="relay-tech-field">
                        <div class="relay-tech-label">Findings</div>
                        <p class="relay-tech-prewrap">{{ $latestDiagnosis->findings }}</p>
                    </div>

                    <div class="relay-tech-field">
                        <div class="relay-tech-label">Recommended Action</div>
                        <p class="relay-tech-prewrap">{{ $latestDiagnosis->recommended_action }}</p>
                    </div>

                    <div class="relay-tech-notice">
                        Diagnosis editing is unavailable
                        at the current ticket stage.
                    </div>
                </div>
            @else
                <div class="relay-tech-notice">
                    Diagnosis cannot be added at this ticket stage.
                </div>
            @endif
        </section>

        <section class="relay-panel relay-tech-section">
            <div class="relay-tech-panel-heading">
                <div>
                    <div class="relay-tech-overline">Internal Communication</div>
                    <h2 class="relay-panel-title">Technician Notes</h2>
                    <p class="relay-section-description">
                        Keep a record of repair progress and technical observations.
                    </p>
                </div>
            </div>

            @unless ($isClosed)
                <form method="POST"
                      action="{{ route('technician.tickets.notes.store', $ticket) }}"
                      class="relay-tech-form">
                    @csrf

                    <div class="relay-tech-field">
                        <label for="note-content"
                               class="relay-tech-label">
                            New Internal Note
                        </label>

                        <textarea id="note-content"
                                  name="content"
                                  class="relay-tech-input relay-tech-textarea"
                                  maxlength="5000"
                                  placeholder="Write a service progress note..."
                                  required>{{ old('content') }}</textarea>
                    </div>

                    <button type="submit"
                            class="relay-tech-submit">
                        Add Note
                    </button>
                </form>
            @endunless

            <div class="relay-tech-note-list">
                @forelse ($ticket->notes as $note)
                    <article class="relay-tech-note">
                        <div class="relay-tech-note-header">
                            <strong>
                                {{ $note->user?->name ?? 'Unknown User' }}
                            </strong>

                            <span class="relay-tech-muted">
                                {{ $note->created_at->format('d M Y, H:i') }}
                            </span>
                        </div>

                        <p class="relay-tech-note-content">{{ $note->content }}</p>
                    </article>
                @empty
                    <div class="relay-tech-empty">
                        No technician notes recorded yet.
                    </div>
                @endforelse
            </div>
        </section>

    </div>

    <aside class="relay-tech-workspace-side">
        <section class="relay-panel relay-tech-section">
            <div class="relay-tech-overline">Audit Trail</div>
            <h2 class="relay-panel-title">Activity Timeline</h2>

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

                        @if ($activity->description)
                            <div class="relay-ticket-activity-description">
                                {{ $activity->description }}
                            </div>
                        @endif

                        @if ($activity->old_status !== $activity->new_status)
                            <div class="relay-ticket-activity-meta">
                                {{ $statusLabels[$activity->old_status] ?? 'New' }}
                                →
                                {{ $statusLabels[$activity->new_status] ?? $activity->new_status }}
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
    </aside>

</div>

@endsection
