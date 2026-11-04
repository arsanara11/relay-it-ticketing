@props([
    'name',
    'active' => false,
    'href' => '#',
    'label' => '',
])

<a
    href="{{ $href }}"
    class="relay-nav-link {{ $active ? 'active' : '' }}"
    title="{{ $label }}"
    aria-label="{{ $label }}"
    @if($active) aria-current="page" @endif
>
    @switch($name)
        @case('dashboard')
            <svg viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-linecap="round"
                 stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="2"/>
                <rect x="14" y="3" width="7" height="7" rx="2"/>
                <rect x="3" y="14" width="7" height="7" rx="2"/>
                <rect x="14" y="14" width="7" height="7" rx="2"/>
            </svg>
            @break

        @case('customers')
            <svg viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-linecap="round"
                 stroke-linejoin="round">
                <circle cx="9" cy="8" r="3"/>
                <path d="M3 20v-2a6 6 0 0 1 12 0v2"/>
                <path d="M17 5a3 3 0 0 1 0 6"/>
                <path d="M18 14a5 5 0 0 1 3 5v1"/>
            </svg>
            @break

        @case('tickets')
            <svg viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-linecap="round"
                 stroke-linejoin="round">
                <path d="M4 4h16v5a3 3 0 0 0 0 6v5H4v-5a3 3 0 0 0 0-6V4Z"/>
                <path d="M12 4v16" stroke-dasharray="3 3"/>
            </svg>
            @break

        @case('devices')
            <svg viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-linecap="round"
                 stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="13" rx="2"/>
                <path d="M8 21h8M12 17v4"/>
            </svg>
            @break

        @case('reports')
            <svg viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-linecap="round"
                 stroke-linejoin="round">
                <path d="M4 20V12M10 20V4M16 20v-9M22 20V7"/>
            </svg>
            @break

        @default
            <svg viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-linecap="round"
                 stroke-linejoin="round">
                <circle cx="12" cy="12" r="9"/>
                <path d="M12 8v4l3 2"/>
            </svg>
    @endswitch
</a>
