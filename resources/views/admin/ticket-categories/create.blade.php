@extends('layouts.app')

@section('title', 'Add Category')
@section('eyebrow', 'Management / Ticket Categories / Create')
@section('page-title', 'Add Category')

@section('content')

<div class="relay-page-actions">

    <p class="relay-section-description">
        Create a new service ticket classification.
    </p>

    <a href="{{ route('admin.ticket-categories.index') }}"
       class="relay-action-link">
        Back to Categories
    </a>

</div>

<section class="relay-panel">

    <h2 class="relay-panel-title">
        Category Information
    </h2>

    <p class="relay-section-description"
       style="margin-bottom: 28px;">
        Enter the category details below.
    </p>

    <form
        method="POST"
        action="{{ route('admin.ticket-categories.store') }}"
    >
        @include('admin.ticket-categories._form')
    </form>

</section>

@endsection
