@extends('layouts.app')

@section('title', 'Add Customer')
@section('eyebrow', 'Management / Customers / Create')
@section('page-title', 'Add Customer')

@section('content')

<div class="relay-page-actions">
    <p class="relay-section-description">
        Register a new customer in the Relay workspace.
    </p>

    <a href="{{ route('admin.customers.index') }}"
       class="relay-action-link">
        Back to Customers
    </a>
</div>

<section class="relay-panel">

    <h2 class="relay-panel-title">
        Customer Information
    </h2>

    <p class="relay-section-description"
       style="margin-bottom: 28px;">
        Fill in the details below to create a customer record.
    </p>

    <form
        method="POST"
        action="{{ route('admin.customers.store') }}"
    >
        @include('admin.customers._form')
    </form>

</section>

@endsection
