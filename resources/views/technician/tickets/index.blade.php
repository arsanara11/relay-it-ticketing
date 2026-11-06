@extends('layouts.app')

@section('title', 'Assigned Tickets')
@section('eyebrow', 'Workspace / Assigned Tickets')
@section('page-title', 'Assigned Tickets')

@section('content')

<div class="relay-page-actions">

    <p class="relay-section-description">
        View and track service tickets assigned to you.
    </p>

    <span class="relay-ticket-badge">
        {{ $totalAssigned }} Total Assigned
    </span>

</div>

<section class="relay-panel">

    <h2 class="relay-panel-title">
        My Service Tickets
    </h2>

    <p class="relay-section-description">
        Search your assignments and filter by service status.
    </p>

    <div class="relay-ticket-toolbar">

        <form method="GET"
              action="{{ route('technician.tickets.index') }}"
              class="relay-search-form">

            <input
                type="search"
                name="search"
                class="relay-input"
                value="{{ $search }}"
                placeholder="Search ticket, customer, device..."
                aria-label="Search assigned tickets"
            >

            <input type="hidden"
                   name="status"
                   value="{{ $status }}">

            <button type="submit"
                    class="relay-button relay-button-secondary">
                Search
            </button>
        </form>

        <form method="GET"
              action="{{ route('technician.tickets.index') }}">

            <input type="hidden"
                   name="search"
                   value="{{ $search }}">

            <select
                name="status"
                class="relay-input relay-ticket-filter"
                onchange="this.form.submit()"
            >
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
                    <th>Received</th>
                    <th>Action</th>
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
                            {{ $ticket->received_at?->format('d M Y') ?? '—' }}
                        </td>

                        <td>
                            <a
                                href="{{ route('technician.tickets.show', $ticket) }}"
                                class="relay-table-action">
                                View Ticket
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="relay-empty">
                                No assigned tickets found.
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
