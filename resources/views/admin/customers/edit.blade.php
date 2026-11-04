@extends('layouts.app')

@section('title', 'Edit Customer')
@section('eyebrow', 'Management / Customers / Edit')
@section('page-title', 'Edit Customer')

@section('content')

<div class="relay-page-actions">
    <p class="relay-section-description">
        Update the information for {{ $customer->name }}.
    </p>

    <a href="{{ route('admin.customers.index') }}"
       class="relay-action-link">
        Back to Customers
    </a>
</div>

<section class="relay-panel">

    <h2 class="relay-panel-title">
        Edit Customer Information
    </h2>

    <p class="relay-section-description"
       style="margin-bottom: 28px;">
        Make changes to the customer record below.
    </p>

    <form
        method="POST"
        action="{{ route('admin.customers.update', $customer) }}"
    >
        @method('PUT')

        @include('admin.customers._form')
    </form>

</section>

@endsection
