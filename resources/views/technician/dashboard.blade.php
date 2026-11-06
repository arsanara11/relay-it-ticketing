@extends('layouts.app')

@section('title', 'Technician Dashboard')
@section('eyebrow', 'Workspace / Technician')
@section('page-title', 'My Workspace')

@section('content')

<div class="relay-tech-heading">

    <div>
        <h2 class="relay-tech-welcome">
            Hello, {{ auth()->user()->name }}.
        </h2>

        <p class="relay-tech-description">
            Here's an overview of your assigned service work.
        </p>
    </div>

    <a href="{{ route('technician.tickets.index') }}"
       class="relay-button">
        View Assigned Tickets
    </a>

</div>

<div class="relay-tech-summary">

    <div class="relay-tech-stat">
        <div class="relay-tech-stat-label">
            Total Assigned
        </div>

        <div class="relay-tech-stat-value">
            {{ $totalAssigned }}
        </div>
    </div>

    <div class="relay-tech-stat">
        <div class="relay-tech-stat-label">
            Active Tickets
        </div>

        <div class="relay-tech-stat-value">
            {{ $activeTickets }}
        </div>
    </div>

    <div class="relay-tech-stat">
        <div class="relay-tech-stat-label">
            Completed
        </div>

        <div class="relay-tech-stat-value">
            {{ $completedTickets }}
        </div>
    </div>

</div>

<section class="relay-panel">

    <div class="relay-tech-heading">
        <div>
            <h2 class="relay-panel-title">
                Priority Queue
            </h2>

            <p class="relay-tech-description">
                Your active tickets, ordered by priority.
            </p>
        </div>

        <a href="{{ route('technician.tickets.index') }}"
           class="relay-action-link">
            View All
        </a>
    </div>

    <div class="relay-tech-ticket-list">

        @forelse ($recentTickets as $ticket)

            <div class="relay-tech-ticket">

                <div class="relay-tech-ticket-main">

                    <div class="relay-tech-ticket-number">
                        {{ $ticket->ticket_number }}
                    </div>

                    <div class="relay-tech-ticket-title">
                        {{ $ticket->subject }}
                    </div>

                    <div class="relay-tech-ticket-meta">
                        {{ $ticket->customer->name }}
                        ·
                        {{ $ticket->device->brand }}
                        {{ $ticket->device->model }}
                    </div>

                </div>

                <div class="relay-tech-ticket-side">

                    <span class="relay-ticket-badge {{ $ticket->priority }}">
                        {{ ucfirst($ticket->priority) }}
                    </span>

                    <a href="{{ route('technician.tickets.show', $ticket) }}"
                       class="relay-table-action">
                        View Ticket
                    </a>

                </div>

            </div>

        @empty

            <div class="relay-tech-empty">
                No active tickets assigned to you yet.
            </div>

        @endforelse

    </div>

</section>

@endsection
