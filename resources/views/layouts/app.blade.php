<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Invoice System</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            pink: '{{ \App\Models\Setting::get("brand_pink", "#ff0878") }}',
                            purple: '{{ \App\Models\Setting::get("brand_purple", "#8035ca") }}',
                            blue: '{{ \App\Models\Setting::get("brand_blue", "#0057be") }}',
                            teal: '{{ \App\Models\Setting::get("brand_teal", "#2fc9c3") }}',
                        },
                        primary: '{{ \App\Models\Setting::get("brand_pink", "#ff0878") }}',
                        secondary: '#0057be', // Using blue as secondary
                        dark: '#1f2937',
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Alpine.js Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Quill Rich Text Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Quill Snow compact toolbar for table rows */
        .ql-toolbar.ql-snow {
            border: none;
            border-bottom: 1px solid #e5e7eb;
            padding: 4px 6px;
            background: #f9fafb;
            border-radius: 0.375rem 0.375rem 0 0;
        }
        .ql-container.ql-snow {
            border: none;
            font-size: 13px;
            min-height: 40px;
        }
        .ql-editor {
            min-height: 40px;
            padding: 6px 8px;
        }
        .ql-editor.ql-blank::before {
            left: 8px;
        }
        /* Wrap the editor+toolbar in a border */
        .quill-wrapper .ql-toolbar,
        .quill-wrapper .ql-container {
            display: block;
        }
        .quill-wrapper {
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            background: white;
        }
        .ql-snow .ql-toolbar button {
            width: 22px;
            height: 22px;
        }
        .ql-snow .ql-toolbar .ql-formats {
            margin-right: 6px;
        }
    </style>
    @stack('head')
</head>

<body class="bg-gray-100 text-gray-800" x-data="{ mobileSidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">
        <!-- Desktop Sidebar -->
        @unless(View::hasSection('no_sidebar'))
        <aside class="w-64 bg-dark text-white flex-shrink-0 hidden md:flex flex-col">
            <div class="p-4 flex items-center justify-center h-20 border-b border-gray-700">
                <img src="{{ asset('images/logo_loops_light.png') }}" alt="Loops Integrated" class="h-12 w-auto">
            </div>
            <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition {{ request()->routeIs('dashboard') ? 'bg-gray-700 text-brand-pink' : '' }}">
                    <i class="fas fa-home w-6"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('petty-cash.index') }}"
                    class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition {{ request()->routeIs('petty-cash.*') ? 'bg-gray-700 text-brand-pink' : '' }}">
                    <i class="fas fa-wallet w-6"></i>
                    <span>Petty Cash</span>
                </a>

                @if(auth()->user()->role !== 'Staff')
                    <a href="{{ route('customers.index') }}"
                        class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition {{ request()->routeIs('customers.*') ? 'bg-gray-700 text-brand-pink' : '' }}">
                        <i class="fas fa-users w-6"></i>
                        <span>Customers</span>
                    </a>
                    <a href="{{ route('deals.index') }}"
                        class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition {{ request()->routeIs('deals.*') ? 'bg-gray-700 text-brand-pink' : '' }}">
                        <i class="fas fa-funnel-dollar w-6"></i>
                        <span>Deals</span>
                    </a>
                    <a href="{{ route('jobs.index') }}"
                        class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition {{ request()->routeIs('jobs.*') ? 'bg-gray-700 text-brand-pink' : '' }}">
                        <i class="fas fa-briefcase w-6"></i>
                        <span>Jobs</span>
                    </a>
                    <a href="{{ route('estimates.index') }}"
                        class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition {{ (request()->routeIs('estimates.*') && request('from') !== 'invoice') ? 'bg-gray-700 text-brand-pink' : '' }}">
                        <i class="fas fa-file-invoice w-6"></i>
                        <span>Estimates</span>
                    </a>
                    <a href="{{ route('invoices.index') }}"
                        class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition {{ (request()->is('invoices*') || request('from') === 'invoice') ? 'bg-gray-700 text-brand-pink' : '' }}">
                        <i class="fas fa-file-invoice-dollar mr-3 w-5"></i> Invoices
                    </a>
                    <a href="{{ route('reports.index') }}"
                        class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition {{ request()->routeIs('reports.*') ? 'bg-gray-700 text-brand-pink' : '' }}">
                        <i class="fas fa-chart-bar w-6"></i>
                        <span>Reports</span>
                    </a>

                    @if(auth()->user()->role === 'Super Admin')
                        <a href="{{ route('users.index') }}"
                            class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition {{ request()->is('users*') ? 'bg-gray-700 text-brand-pink' : '' }}">
                            <i class="fas fa-users-cog mr-3 w-5"></i> Users
                        </a>

                        <a href="{{ route('activities.index') }}"
                            class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition {{ request()->routeIs('activities.*') ? 'bg-gray-700 text-brand-pink' : '' }}">
                            <i class="fas fa-history mr-3 w-5"></i> Activity Log
                        </a>

                        <a href="{{ route('settings.index') }}"
                            class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition {{ request()->is('settings*') ? 'bg-gray-700 text-brand-pink' : '' }}">
                            <i class="fas fa-cog mr-3 w-5"></i> Settings
                        </a>
                    @endif
                @endif
            </nav>
            <div class="p-4 border-t border-gray-700">
                <div class="flex items-center space-x-2">
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'Admin' }}&background=random"
                        alt="User" class="w-8 h-8 rounded-full">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ Auth::user()->name ?? 'Admin User' }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email ?? 'admin@example.com' }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-white" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Mobile Drawer Sidebar (Sliding) -->
        <div x-show="mobileSidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-gray-900/60 backdrop-blur-sm md:hidden" 
             @click="mobileSidebarOpen = false" 
             style="display: none;"></div>

        <aside x-show="mobileSidebarOpen"
               x-transition:enter="transition ease-in-out duration-300 transform"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in-out duration-300 transform"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-dark text-white flex flex-col md:hidden shadow-2xl"
               style="display: none;">
            <div class="p-4 flex items-center justify-between h-20 border-b border-gray-700">
                <img src="{{ asset('images/logo_loops_light.png') }}" alt="Loops Integrated" class="h-10 w-auto">
                <button @click="mobileSidebarOpen = false" class="text-gray-400 hover:text-white p-2 rounded-lg focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <nav class="flex-1 px-3 py-4 space-y-2 overflow-y-auto">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition {{ request()->routeIs('dashboard') ? 'bg-gray-700 text-brand-pink font-semibold' : '' }}">
                    <i class="fas fa-home w-6"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('petty-cash.index') }}"
                    class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition {{ request()->routeIs('petty-cash.*') ? 'bg-gray-700 text-brand-pink font-semibold' : '' }}">
                    <i class="fas fa-wallet w-6"></i>
                    <span>Petty Cash</span>
                </a>

                @if(auth()->user()->role !== 'Staff')
                    <a href="{{ route('customers.index') }}"
                        class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition {{ request()->routeIs('customers.*') ? 'bg-gray-700 text-brand-pink font-semibold' : '' }}">
                        <i class="fas fa-users w-6"></i>
                        <span>Customers</span>
                    </a>
                    <a href="{{ route('deals.index') }}"
                        class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition {{ request()->routeIs('deals.*') ? 'bg-gray-700 text-brand-pink font-semibold' : '' }}">
                        <i class="fas fa-funnel-dollar w-6"></i>
                        <span>Deals</span>
                    </a>
                    <a href="{{ route('jobs.index') }}"
                        class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition {{ request()->routeIs('jobs.*') ? 'bg-gray-700 text-brand-pink font-semibold' : '' }}">
                        <i class="fas fa-briefcase w-6"></i>
                        <span>Jobs</span>
                    </a>
                    <a href="{{ route('estimates.index') }}"
                        class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition {{ (request()->routeIs('estimates.*') && request('from') !== 'invoice') ? 'bg-gray-700 text-brand-pink font-semibold' : '' }}">
                        <i class="fas fa-file-invoice w-6"></i>
                        <span>Estimates</span>
                    </a>
                    <a href="{{ route('invoices.index') }}"
                        class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition {{ (request()->is('invoices*') || request('from') === 'invoice') ? 'bg-gray-700 text-brand-pink font-semibold' : '' }}">
                        <i class="fas fa-file-invoice-dollar mr-3 w-5"></i> Invoices
                    </a>
                    <a href="{{ route('reports.index') }}"
                        class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition {{ request()->routeIs('reports.*') ? 'bg-gray-700 text-brand-pink font-semibold' : '' }}">
                        <i class="fas fa-chart-bar w-6"></i>
                        <span>Reports</span>
                    </a>

                    @if(auth()->user()->role === 'Super Admin')
                        <a href="{{ route('users.index') }}"
                            class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition {{ request()->is('users*') ? 'bg-gray-700 text-brand-pink font-semibold' : '' }}">
                            <i class="fas fa-users-cog mr-3 w-5"></i> Users
                        </a>

                        <a href="{{ route('activities.index') }}"
                            class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition {{ request()->routeIs('activities.*') ? 'bg-gray-700 text-brand-pink font-semibold' : '' }}">
                            <i class="fas fa-history mr-3 w-5"></i> Activity Log
                        </a>

                        <a href="{{ route('settings.index') }}"
                            class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition {{ request()->is('settings*') ? 'bg-gray-700 text-brand-pink font-semibold' : '' }}">
                            <i class="fas fa-cog mr-3 w-5"></i> Settings
                        </a>
                    @endif
                @endif
            </nav>
            <div class="p-4 border-t border-gray-700 bg-gray-900/50">
                <div class="flex items-center space-x-3">
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'Admin' }}&background=random"
                        alt="User" class="w-10 h-10 rounded-full border-2 border-brand-pink/50">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name ?? 'Admin User' }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email ?? 'admin@example.com' }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="p-2 text-gray-400 hover:text-white rounded-lg" title="Logout">
                            <i class="fas fa-sign-out-alt text-lg"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        @endunless

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm relative z-40">
                <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4">
                    <div class="flex items-center flex-1 mr-4 min-w-0">
                        @unless(View::hasSection('no_sidebar'))
                        <button @click="mobileSidebarOpen = !mobileSidebarOpen" 
                                class="md:hidden text-gray-600 hover:text-gray-900 p-2 rounded-lg hover:bg-gray-100 focus:outline-none transition-colors mr-2">
                            <i class="fas fa-bars text-lg"></i>
                        </button>
                        @endunless
                        <h2 class="text-lg sm:text-xl font-bold text-gray-800 truncate">@yield('header')</h2>
                    </div>
                    <div>
                        <div x-data="{ 
                            open: false, 
                            unreadCount: {{ auth()->user()->unreadNotifications->count() }},
                            markRead() {
                                this.open = !this.open;
                                if (this.open && this.unreadCount > 0) {
                                    fetch('{{ route('notifications.markAsRead') }}', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                        },
                                    }).then(() => {
                                        this.unreadCount = 0;
                                        // Optionally dim unread items immediately
                                        document.querySelectorAll('.notification-item-unread').forEach(el => {
                                            el.classList.remove('bg-blue-50', 'notification-item-unread');
                                            el.classList.add('opacity-60');
                                        });
                                    });
                                }
                            }
                        }" class="relative">
                        <button @click="markRead()"
                            class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none transition-colors">
                            <i class="fas fa-bell text-lg"></i>
                            <template x-if="unreadCount > 0">
                                <span
                                    class="absolute top-1 right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-red-100 transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full"
                                    x-text="unreadCount">
                                </span>
                            </template>
                        </button>

                        <!-- Notification Dropdown -->
                        <div x-show="open" @click.away="open = false"
                            class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-xl shadow-2xl overflow-hidden z-50 border border-gray-200"
                            style="display: none;">
                            <div class="py-2">
                                 <div class="px-4 py-2 border-b border-gray-200 text-sm font-semibold text-gray-700 flex justify-between items-center">
                                    <span>Notifications</span>
                                    @if(auth()->user()->unreadNotifications->count() > 0)
                                        <button onclick="markAllAsRead()" class="text-xs text-brand-blue hover:underline">Mark all as read</button>
                                    @endif
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    @forelse(auth()->user()->notifications->take(10) as $notification)
                                        <a href="{{ isset($notification->data['deal_id']) ? route('deals.index') : (isset($notification->data['petty_cash_id']) ? (auth()->user()->role === 'Staff' ? route('dashboard') : route('petty-cash.index')) : (isset($notification->data['request_id']) ? route('customers.requests.review', $notification->data['request_id']) : (isset($notification->data['customer_id']) ? route('customers.edit', $notification->data['customer_id']) : (isset($notification->data['invoice_id']) ? route('invoices.show', $notification->data['invoice_id']) : '#')))) }}"
                                            class="block px-4 py-3 hover:bg-gray-50 transition duration-150 ease-in-out border-b border-gray-100 last:border-b-0 {{ $notification->read_at ? 'opacity-60' : 'bg-blue-50 notification-item-unread' }}">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $notification->data['message'] ?? 'New Notification' }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </p>
                                        </a>
                                    @empty
                                        <div class="px-4 py-3 text-sm text-gray-500 text-center">
                                            No notifications
                                        </div>
                                    @endforelse
                                </div>
                                <div class="border-t border-gray-200 bg-gray-50 px-4 py-2 text-center">
                                    <a href="#" class="text-xs font-medium text-brand-blue hover:text-brand-purple">View all</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 sm:p-6">
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                        role="alert">
                        <strong class="font-bold">Success!</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
    <script>
        function markAllAsRead() {
            fetch('{{ route('notifications.markAsRead') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Clear dark mode preference if previously set
        localStorage.removeItem('staff_dark_mode');
        document.body.classList.remove('dark');
    </script>
</body>

</html>