@extends('layouts.app')

@section('title', 'Category Details')
@section('eyebrow', 'Management / Ticket Categories / Details')
@section('page-title', 'Category Details')

@section('content')

<div class="relay-page-actions">

    <p class="relay-section-description">
        View category information and associated service tickets.
    </p>

    <div class="relay-row-actions">

        <a href="{{ route('admin.ticket-categories.index') }}"
           class="relay-action-link">
            Back
        </a>

        <a href="{{ route('admin.ticket-categories.edit', $ticketCategory) }}"
           class="relay-button">
            Edit Category
        </a>

    </div>

</div>

<section class="relay-panel">

    <div class="relay-category-detail-header">

        <div class="relay-category-icon">
            <svg viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.8"
                 stroke-linecap="round"
                 stroke-linejoin="round">
                <path d="M20 13 13 20a2 2 0 0 1-2.83 0L3 12.83V4h8.83L20 12.17a2 2 0 0 1 0 2.83Z"/>
                <circle cx="7.5" cy="8.5" r="1.5"/>
            </svg>
        </div>

        <div>
            <h2 class="relay-category-detail-title">
                {{ $ticketCategory->name }}
            </h2>

            <div class="relay-category-detail-subtitle">
                Category #{{ str_pad($ticketCategory->id, 4, '0', STR_PAD_LEFT) }}
            </div>
        </div>

    </div>

    <div class="relay-detail-grid">

        <div>
            <div class="relay-detail-label">Category Name</div>
            <div class="relay-detail-value">
                {{ $ticketCategory->name }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Total Tickets</div>
            <div class="relay-detail-value">
                {{ $ticketCount }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Created On</div>
            <div class="relay-detail-value">
                {{ $ticketCategory->created_at->format('d M Y') }}
            </div>
        </div>

        <div>
            <div class="relay-detail-label">Last Updated</div>
            <div class="relay-detail-value">
                {{ $ticketCategory->updated_at->format('d M Y') }}
            </div>
        </div>

        <div style="grid-column: 1 / -1;">
            <div class="relay-detail-label">Description</div>
            <div class="relay-detail-value"
                 style="white-space: pre-wrap;">{{ $ticketCategory->description ?: 'No description provided.' }}</div>
        </div>

    </div>

</section>

<section class="relay-panel relay-detail-section">

    <h2 class="relay-panel-title">
        Recent Service Tickets
    </h2>

    <p class="relay-section-description">
        Latest tickets associated with this category.
    </p>

    <div class="relay-detail-list">

        @forelse ($ticketCategory->tickets as $ticket)

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
                No service tickets in this category yet.
            </div>

        @endforelse

    </div>

</section>

@endsection
