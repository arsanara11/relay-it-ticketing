@extends('layouts.app')

@section('title', 'Edit Service Ticket')
@section('eyebrow', 'Operations / Service Orders / Edit')
@section('page-title', 'Edit Service Ticket')

@section('content')

<div class="relay-page-actions">
    <p class="relay-section-description">
        Update service order {{ $ticket->ticket_number }}.
    </p>

    <a href="{{ route('admin.tickets.show', $ticket) }}"
       class="relay-action-link">
        Back to Ticket
    </a>
</div>

<section class="relay-panel">

    <h2 class="relay-panel-title">Service Information</h2>

    <p class="relay-section-description"
       style="margin-bottom: 28px;">
        Update ticket details or change the assigned technician.
    </p>

    <form method="POST"
          action="{{ route('admin.tickets.update', $ticket) }}">
        @method('PUT')

        @include('admin.tickets._form')
    </form>

</section>

@endsection
