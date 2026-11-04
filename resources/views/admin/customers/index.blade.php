<h1>Customers</h1>

<a href="{{ route('admin.dashboard') }}">
    Dashboard
</a>

<a href="{{ route('admin.customers.create') }}">
    Add Customer
</a>

@if (session('success'))
    <p>{{ session('success') }}</p>
@endif

@if (session('error'))
    <p>{{ session('error') }}</p>
@endif

<form method="GET"
      action="{{ route('admin.customers.index') }}">
    <input
        type="search"
        name="search"
        value="{{ $search }}"
        placeholder="Search customers..."
    >
    <button type="submit">Search</button>
</form>

<table border="1" cellpadding="10">
    <thead>
        <tr>
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($customers as $customer)
            <tr>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->phone }}</td>
                <td>{{ $customer->email ?? '-' }}</td>
                <td>
                    <a href="{{ route('admin.customers.show', $customer) }}">
                        View
                    </a>

                    <a href="{{ route('admin.customers.edit', $customer) }}">
                        Edit
                    </a>

                    <form
                        method="POST"
                        action="{{ route('admin.customers.destroy', $customer) }}"
                        style="display:inline"
                        onsubmit="return confirm('Delete this customer?')"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No customers found.</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{ $customers->links() }}
