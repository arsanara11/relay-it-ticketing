@csrf

<div>
    <label for="name">Customer Name</label>
    <input
        id="name"
        type="text"
        name="name"
        value="{{ old('name', $customer->name ?? '') }}"
        maxlength="150"
        required
    >
    @error('name')
        <p>{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="phone">Phone Number</label>
    <input
        id="phone"
        type="text"
        name="phone"
        value="{{ old('phone', $customer->phone ?? '') }}"
        maxlength="20"
        required
    >
    @error('phone')
        <p>{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="email">Email</label>
    <input
        id="email"
        type="email"
        name="email"
        value="{{ old('email', $customer->email ?? '') }}"
    >
    @error('email')
        <p>{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="address">Address</label>
    <textarea
        id="address"
        name="address"
    >{{ old('address', $customer->address ?? '') }}</textarea>
    @error('address')
        <p>{{ $message }}</p>
    @enderror
</div>

<button type="submit">Save Customer</button>
