@csrf

<div class="relay-form-grid">

    <div class="relay-form-field">
        <label for="name" class="relay-form-label">
            Full Name <span class="relay-required">*</span>
        </label>

        <input
            id="name"
            type="text"
            name="name"
            class="relay-input"
            value="{{ old('name', $customer->name ?? '') }}"
            placeholder="Enter customer name"
            maxlength="150"
            required
        >

        @error('name')
            <span class="relay-form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="relay-form-field">
        <label for="phone" class="relay-form-label">
            Phone Number <span class="relay-required">*</span>
        </label>

        <input
            id="phone"
            type="tel"
            name="phone"
            class="relay-input"
            value="{{ old('phone', $customer->phone ?? '') }}"
            placeholder="e.g. 081234567890"
            maxlength="20"
            required
        >

        @error('phone')
            <span class="relay-form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="relay-form-field full">
        <label for="email" class="relay-form-label">
            Email Address
        </label>

        <input
            id="email"
            type="email"
            name="email"
            class="relay-input"
            value="{{ old('email', $customer->email ?? '') }}"
            placeholder="customer@example.com"
        >

        @error('email')
            <span class="relay-form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="relay-form-field full">
        <label for="address" class="relay-form-label">
            Address
        </label>

        <textarea
            id="address"
            name="address"
            class="relay-input"
            placeholder="Enter customer address"
            rows="4"
            maxlength="2000"
        >{{ old('address', $customer->address ?? '') }}</textarea>

        @error('address')
            <span class="relay-form-error">{{ $message }}</span>
        @enderror
    </div>

</div>

<div class="relay-form-actions">

    <a href="{{ route('admin.customers.index') }}"
       class="relay-action-link">
        Cancel
    </a>

    <button type="submit" class="relay-button">
        Save Customer
    </button>

</div>
