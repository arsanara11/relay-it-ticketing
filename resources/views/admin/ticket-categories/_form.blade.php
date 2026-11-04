@csrf

<div class="relay-form-grid">

    <div class="relay-form-field full">
        <label for="name" class="relay-form-label">
            Category Name <span class="relay-required">*</span>
        </label>

        <input
            id="name"
            type="text"
            name="name"
            class="relay-input"
            value="{{ old('name', $ticketCategory->name ?? '') }}"
            placeholder="e.g. Hardware Repair"
            maxlength="150"
            required
        >

        @error('name')
            <span class="relay-form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="relay-form-field full">
        <label for="description" class="relay-form-label">
            Description
        </label>

        <textarea
            id="description"
            name="description"
            class="relay-input"
            rows="4"
            maxlength="2000"
            placeholder="Describe the type of service or issue..."
        >{{ old('description', $ticketCategory->description ?? '') }}</textarea>

        @error('description')
            <span class="relay-form-error">{{ $message }}</span>
        @enderror

        <span class="relay-device-form-hint">
            This description helps administrators choose the
            correct category when creating service tickets.
        </span>
    </div>

</div>

<div class="relay-form-actions">

    <a href="{{ route('admin.ticket-categories.index') }}"
       class="relay-action-link">
        Cancel
    </a>

    <button type="submit" class="relay-button">
        Save Category
    </button>

</div>
