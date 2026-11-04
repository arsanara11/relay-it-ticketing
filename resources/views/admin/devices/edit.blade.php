@extends('layouts.app')

@section('title', 'Edit Device')
@section('eyebrow', 'Management / Devices / Edit')
@section('page-title', 'Edit Device')

@section('content')

<div class="relay-page-actions">

    <p class="relay-section-description">
        Update information for
        {{ $device->brand }} {{ $device->model }}.
    </p>

    <a href="{{ route('admin.devices.index') }}"
       class="relay-action-link">
        Back to Devices
    </a>

</div>

<section class="relay-panel">

    <h2 class="relay-panel-title">
        Edit Device Information
    </h2>

    <p class="relay-section-description"
       style="margin-bottom: 28px;">
        Modify the registered device information below.
    </p>

    <form
        method="POST"
        action="{{ route('admin.devices.update', $device) }}"
    >
        @method('PUT')

        @include('admin.devices._form')
    </form>

</section>

@endsection
