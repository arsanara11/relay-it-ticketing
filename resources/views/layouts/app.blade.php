<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard') — Relay</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="relay-shell">

        <aside class="relay-sidebar">

            <a href="{{ route('dashboard') }}"
               class="relay-logo"
               title="Relay">
                r.
            </a>

            <nav class="relay-nav"
                 aria-label="Main navigation">

                <x-sidebar-icon
                    name="dashboard"
                    label="Dashboard"
                    :href="route('dashboard')"
                    :active="request()->routeIs('admin.dashboard', 'technician.dashboard')"
                />

                @if (auth()->user()->isAdmin())
                    <x-sidebar-icon
                        name="tickets"
                        label="Service Orders"
                        href="#"
                    />

                    <x-sidebar-icon
                        name="customers"
                        label="Customers"
                        :href="route('admin.customers.index')"
                        :active="request()->routeIs('admin.customers.*')"
                    />

                    <x-sidebar-icon
                        name="devices"
                        label="Devices"
                        href="#"
                    />

                    <x-sidebar-icon
                        name="reports"
                        label="Reports"
                        href="#"
                    />
                @endif

            </nav>

            <div class="relay-nav-bottom">
                <form method="POST"
                      action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="relay-nav-link"
                        title="Logout"
                        aria-label="Logout"
                    >
                        <svg viewBox="0 0 24 24"
                             width="21"
                             height="21"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M10 17l5-5-5-5"/>
                            <path d="M15 12H3"/>
                            <path d="M12 3h6a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3h-6"/>
                        </svg>
                    </button>
                </form>
            </div>

        </aside>

        <main class="relay-main">

            <header class="relay-topbar">
                <div>
                    <div class="relay-eyebrow">
                        @yield('eyebrow', 'Relay Workspace')
                    </div>

                    <h1 class="relay-page-title">
                        @yield('page-title', 'Dashboard')
                    </h1>
                </div>

                <div class="relay-user">
                    <div>
                        <div class="relay-user-name">
                            {{ auth()->user()->name }}
                        </div>

                        <div class="relay-user-role">
                            {{ auth()->user()->role }}
                        </div>
                    </div>

                    <div class="relay-user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </div>
            </header>

            @yield('content')

        </main>
    </div>
</body>
</html>
