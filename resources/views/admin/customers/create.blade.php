<h1>Create Customer</h1>

<a href="{{ route('admin.customers.index') }}">
    Back to Customers
</a>

<form method="POST"
      action="{{ route('admin.customers.store') }}">
    @include('admin.customers._form')
</form>
