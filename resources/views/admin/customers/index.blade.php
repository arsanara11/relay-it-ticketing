@extends('layouts.app')

@section('title', 'Customers')
@section('eyebrow', 'Management / Customers')
@section('page-title', 'Customers')

@section('content')

@if (session('success'))
    <div class="relay-alert success" role="status">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="relay-alert error" role="alert">
        {{ session('error') }}
    </div>
@endif

<div class="relay-page-actions">
    <div>
        <p class="relay-section-description">
            Manage customer information and service records
            in one connected workspace.
        </p>
    </div>

    <a href="{{ route('admin.customers.create') }}"
       class="relay-button">
        + Add Customer
    </a>
</div>

<section class="relay-panel">

    <h2 class="relay-panel-title">
        Customer Directory
    </h2>

    <p class="relay-section-description">
        Search, view, and manage registered customers.
    </p>

    <div class="relay-customer-toolbar">

        <form method="GET"
              action="{{ route('admin.customers.index') }}"
              class="relay-search-form">

            <input
                type="search"
                name="search"
                class="relay-input"
                value="{{ $search }}"
                placeholder="Search name, phone, or email..."
                aria-label="Search customers"
            >

            <button type="submit"
                    class="relay-button relay-button-secondary">
                Search
            </button>

        </form>

        <span class="relay-customer-count">
            {{ $customers->total() }} customers
        </span>

    </div>

    <div class="relay-table-wrapper">
        <table class="relay-table">

            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td>
                            <div class="relay-customer-identity">

                                <div class="relay-customer-avatar">
                                    {{ strtoupper(
                                        substr($customer->name, 0, 1)
                                    ) }}
                                </div>

                                <div>
                                    <div class="relay-customer-name">
                                        {{ $customer->name }}
                                    </div>

                                    <div class="relay-customer-id">
                                        Customer #{{ str_pad(
                                            $customer->id,
                                            4,
                                            '0',
                                            STR_PAD_LEFT
                                        ) }}
                                    </div>
                                </div>

                            </div>
                        </td>

                        <td>{{ $customer->phone }}</td>

                        <td>
                            {{ $customer->email ?? '—' }}
                        </td>

                        <td>
                            {{ $customer->created_at->format('d M Y') }}
                        </td>

                        <td>
                            <div class="relay-row-actions">

                                <a
                                    href="{{ route('admin.customers.show', $customer) }}"
                                    class="relay-table-action">
                                    View
                                </a>

                                <a
                                    href="{{ route('admin.customers.edit', $customer) }}"
                                    class="relay-table-action">
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.customers.destroy', $customer) }}"
                                    onsubmit="return confirm('Are you sure you want to delete this customer?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="relay-table-action danger">
                                        Delete
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="relay-empty">
                                No customers found.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    @if ($customers->hasPages())
        <div class="relay-pagination">
            {{ $customers->links() }}
        </div>
    @endif

</section>

@endsection
