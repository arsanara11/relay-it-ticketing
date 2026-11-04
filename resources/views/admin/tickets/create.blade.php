@extends('layouts.app')

@section('title', 'New Service Ticket')
@section('eyebrow', 'Operations / Service Orders / Create')
@section('page-title', 'New Service Ticket')

@section('content')

<div class="relay-page-actions">
    <p class="relay-section-description">
        Register a new repair request and assign a technician.
    </p>

    <a href="{{ route('admin.tickets.index') }}"
       class="relay-action-link">
        Back to Service Orders
    </a>
</div>

<section class="relay-panel">

    <h2 class="relay-panel-title">Service Information</h2>

    <p class="relay-section-description"
       style="margin-bottom: 28px;">
        The ticket number will be generated automatically.
    </p>

    @if ($customers->isEmpty() || $devices->isEmpty() || $categories->isEmpty())
        <div class="relay-ticket-warning">
            Before creating a ticket, register at least one customer,
            one device, and one ticket category.
        </div>
    @else
        <form method="POST"
              action="{{ route('admin.tickets.store') }}">
            @include('admin.tickets._form')
        </form>
    @endif

</section>

@endsection
