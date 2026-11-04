@extends('layouts.app')

@section('title', 'Add Device')
@section('eyebrow', 'Management / Devices / Create')
@section('page-title', 'Add Device')

@section('content')

<div class="relay-page-actions">

    <p class="relay-section-description">
        Register a customer-owned device in the Relay workspace.
    </p>

    <a href="{{ route('admin.devices.index') }}"
       class="relay-action-link">
        Back to Devices
    </a>

</div>

<section class="relay-panel">

    <h2 class="relay-panel-title">
        Device Information
    </h2>

    <p class="relay-section-description"
       style="margin-bottom: 28px;">
        Enter the device details and select its owner.
    </p>

    @if ($customers->isEmpty())
        <div class="relay-alert error">
            No customers are registered yet.
            Create a customer before adding a device.
        </div>

        <a href="{{ route('admin.customers.create') }}"
           class="relay-button">
            + Add Customer
        </a>
    @else
        <form
            method="POST"
            action="{{ route('admin.devices.store') }}"
        >
            @include('admin.devices._form')
        </form>
    @endif

</section>

@endsection
