<h1>Edit Customer</h1>

<a href="{{ route('admin.customers.index') }}">
    Back to Customers
</a>

<form method="POST"
      action="{{ route('admin.customers.update', $customer) }}">
    @method('PUT')
    @include('admin.customers._form')
</form>
