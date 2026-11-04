@extends('layouts.app')

@section('title', 'Ticket Categories')
@section('eyebrow', 'Management / Ticket Categories')
@section('page-title', 'Ticket Categories')

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

    <p class="relay-section-description">
        Organize service tickets into meaningful categories.
    </p>

    <a href="{{ route('admin.ticket-categories.create') }}"
       class="relay-button">
        + Add Category
    </a>

</div>

<div class="relay-category-summary">

    <div class="relay-category-stat">
        <div class="relay-category-stat-label">
            Total Categories
        </div>
        <div class="relay-category-stat-value">
            {{ $totalCategories }}
        </div>
    </div>

    <div class="relay-category-stat">
        <div class="relay-category-stat-label">
            Categories in Use
        </div>
        <div class="relay-category-stat-value">
            {{ $usedCategories }}
        </div>
    </div>

    <div class="relay-category-stat">
        <div class="relay-category-stat-label">
            Total Service Tickets
        </div>
        <div class="relay-category-stat-value">
            {{ $totalTickets }}
        </div>
    </div>

</div>

<section class="relay-panel">

    <h2 class="relay-panel-title">
        Category Directory
    </h2>

    <p class="relay-section-description">
        Search, create, and manage ticket classifications.
    </p>

    <div class="relay-customer-toolbar">

        <form method="GET"
              action="{{ route('admin.ticket-categories.index') }}"
              class="relay-search-form">

            <input
                type="search"
                name="search"
                class="relay-input"
                value="{{ $search }}"
                placeholder="Search categories..."
                aria-label="Search categories"
            >

            <button type="submit"
                    class="relay-button relay-button-secondary">
                Search
            </button>

        </form>

        <span class="relay-customer-count">
            {{ $categories->total() }} categories
        </span>

    </div>

    <div class="relay-table-wrapper">
        <table class="relay-table">

            <thead>
                <tr>
                    <th>Category</th>
                    <th>Tickets</th>
                    <th>Usage</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td>
                            <div class="relay-category-identity">

                                <div class="relay-category-icon">
                                    <svg viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.8"
                                         stroke-linecap="round"
                                         stroke-linejoin="round">
                                        <path d="M20 13 13 20a2 2 0 0 1-2.83 0L3 12.83V4h8.83L20 12.17a2 2 0 0 1 0 2.83Z"/>
                                        <circle cx="7.5" cy="8.5" r="1.5"/>
                                    </svg>
                                </div>

                                <div>
                                    <div class="relay-category-name">
                                        {{ $category->name }}
                                    </div>

                                    <div class="relay-category-description">
                                        {{ $category->description ?: 'No description provided.' }}
                                    </div>
                                </div>

                            </div>
                        </td>

                        <td>{{ $category->tickets_count }}</td>

                        <td>
                            <span class="relay-category-badge {{ $category->tickets_count > 0 ? 'used' : '' }}">
                                {{ $category->tickets_count > 0 ? 'In Use' : 'Available' }}
                            </span>
                        </td>

                        <td>
                            {{ $category->created_at->format('d M Y') }}
                        </td>

                        <td>
                            <div class="relay-row-actions">

                                <a
                                    href="{{ route('admin.ticket-categories.show', $category) }}"
                                    class="relay-table-action">
                                    View
                                </a>

                                <a
                                    href="{{ route('admin.ticket-categories.edit', $category) }}"
                                    class="relay-table-action">
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.ticket-categories.destroy', $category) }}"
                                    onsubmit="return confirm('Delete this category?')"
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
                                No categories found.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    @if ($categories->hasPages())
        <div class="relay-pagination">
            {{ $categories->links() }}
        </div>
    @endif

</section>

@endsection
