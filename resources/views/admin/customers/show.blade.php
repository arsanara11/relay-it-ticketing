<h1>Customer Details</h1>

<a href="{{ route('admin.customers.index') }}">
    Back to Customers
</a>

<h2>{{ $customer->name }}</h2>

<p>Phone: {{ $customer->phone }}</p>
<p>Email: {{ $customer->email ?? '-' }}</p>
<p>Address: {{ $customer->address ?? '-' }}</p>

<h3>Registered Devices</h3>
<ul>
    @forelse ($customer->devices as $device)
        <li>
            {{ $device->brand }} {{ $device->model }}
            ({{ $device->type }})
        </li>
    @empty
        <li>No devices registered.</li>
    @endforelse
</ul>

<h3>Service Tickets</h3>
<ul>
    @forelse ($customer->tickets as $ticket)
        <li>
            {{ $ticket->ticket_number }}
            — {{ $ticket->subject }}
        </li>
    @empty
        <li>No service tickets yet.</li>
    @endforelse
</ul>
