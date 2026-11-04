@extends('layouts.app')

@section('title', 'Service Orders')
@section('eyebrow', 'Operations / Service Orders')
@section('page-title', 'Service Orders')

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
        Track incoming repairs, assign technicians,
        and manage service tickets.
    </p>

    <a href="{{ route('admin.tickets.create') }}"
       class="relay-button">
        + New Ticket
    </a>
</div>

<div class="relay-ticket-summary">

    <div class="relay-ticket-stat">
        <div class="relay-ticket-stat-label">Total Tickets</div>
        <div class="relay-ticket-stat-value">{{ $totalTickets }}</div>
    </div>

    <div class="relay-ticket-stat">
        <div class="relay-ticket-stat-label">Active Tickets</div>
        <div class="relay-ticket-stat-value">{{ $activeTickets }}</div>
    </div>

    <div class="relay-ticket-stat">
        <div class="relay-ticket-stat-label">Completed</div>
        <div class="relay-ticket-stat-value">{{ $completedTickets }}</div>
    </div>

</div>

<section class="relay-panel">

    <h2 class="relay-panel-title">Service Order Directory</h2>

    <p class="relay-section-description">
        Search and monitor all registered service tickets.
    </p>

    <div class="relay-ticket-toolbar">

        <form method="GET"
              action="{{ route('admin.tickets.index') }}"
              class="relay-search-form">

            <input
                type="search"
                name="search"
                class="relay-input"
                value="{{ $search }}"
                placeholder="Search ticket, customer, device..."
            >

            <input type="hidden" name="status" value="{{ $status }}">

            <button type="submit"
                    class="relay-button relay-button-secondary">
                Search
            </button>
        </form>

        <form method="GET"
              action="{{ route('admin.tickets.index') }}">

            <input type="hidden" name="search" value="{{ $search }}">

            <select name="status"
                    class="relay-input relay-ticket-filter"
                    onchange="this.form.submit()">

                <option value="">All Statuses</option>

                @foreach (\App\Models\Ticket::STATUSES as $option)
                    <option value="{{ $option }}"
                        @selected($status === $option)>
                        {{ ucwords(str_replace('_', ' ', $option)) }}
                    </option>
                @endforeach

            </select>
        </form>

    </div>

    <div class="relay-table-wrapper">
        <table class="relay-table">

            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Customer / Device</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Technician</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($tickets as $ticket)
                    <tr>
                        <td>
                            <div class="relay-ticket-number">
                                {{ $ticket->ticket_number }}
                            </div>

                            <div class="relay-ticket-subject">
                                {{ $ticket->subject }}
                            </div>

                            <div class="relay-ticket-meta">
                                {{ $ticket->created_at->format('d M Y') }}
                            </div>
                        </td>

                        <td>
                            <div class="relay-device-owner">
                                {{ $ticket->customer->name }}
                            </div>

                            <div class="relay-ticket-meta">
                                {{ $ticket->device->brand }}
                                {{ $ticket->device->model }}
                            </div>
                        </td>

                        <td>
                            <span class="relay-ticket-badge {{ $ticket->priority }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </td>

                        <td>
                            <span class="relay-ticket-badge {{ $ticket->status }}">
                                {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </td>

                        <td>
                            {{ $ticket->technician?->name ?? 'Unassigned' }}
                        </td>

                        <td>
                            <div class="relay-row-actions">
                                <a href="{{ route('admin.tickets.show', $ticket) }}"
                                   class="relay-table-action">
                                    View
                                </a>

                                @unless (in_array($ticket->status, ['completed', 'cancelled']))
                                    <a href="{{ route('admin.tickets.edit', $ticket) }}"
                                       class="relay-table-action">
                                        Edit
                                    </a>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="relay-empty">
                                No service tickets found.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    @if ($tickets->hasPages())
        <div class="relay-pagination">
            {{ $tickets->links() }}
        </div>
    @endif

</section>

@endsection
