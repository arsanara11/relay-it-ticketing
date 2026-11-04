@extends('layouts.app')

@section('title', 'Dashboard')
@section('eyebrow', 'Overview / Administration')
@section('page-title', 'Dashboard')

@section('content')

<div class="relay-panel">

    <div class="relay-eyebrow">
        WELCOME BACK
    </div>

    <h2 style="
        font-size: 26px;
        margin: 14px 0 8px;
        letter-spacing: -1px;
    ">
        Hello, {{ auth()->user()->name }}.
    </h2>

    <p class="relay-muted" style="font-size: 13px;">
        Your Relay workspace is ready.
        Manage customers, service orders,
        and repair operations from here.
    </p>

    <div style="margin-top: 28px;">
        <a href="{{ route('admin.customers.index') }}"
           class="relay-button">
            Manage Customers
        </a>
    </div>

</div>

@endsection
