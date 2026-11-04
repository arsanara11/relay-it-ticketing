@extends('layouts.app')

@section('title', 'Edit Category')
@section('eyebrow', 'Management / Ticket Categories / Edit')
@section('page-title', 'Edit Category')

@section('content')

<div class="relay-page-actions">

    <p class="relay-section-description">
        Update the classification details for
        {{ $ticketCategory->name }}.
    </p>

    <a href="{{ route('admin.ticket-categories.index') }}"
       class="relay-action-link">
        Back to Categories
    </a>

</div>

<section class="relay-panel">

    <h2 class="relay-panel-title">
        Edit Category Information
    </h2>

    <p class="relay-section-description"
       style="margin-bottom: 28px;">
        Modify the category name or description.
    </p>

    <form
        method="POST"
        action="{{ route('admin.ticket-categories.update', $ticketCategory) }}"
    >
        @method('PUT')

        @include('admin.ticket-categories._form')
    </form>

</section>

@endsection
