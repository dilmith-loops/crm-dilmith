<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Invoice System</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    
    <!-- PWA Web App Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#8035ca">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Loops CRM">
    <link rel="apple-touch-icon" href="{{ asset('images/pwa-icon-192.png') }}">
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

                @if(auth()->check() && auth()->user()->role !== 'Staff')
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

                    @if(auth()->check() && auth()->user()->role === 'Super Admin')
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
                    <div class="flex items-center space-x-1">
                        <button type="button" onclick="document.getElementById('changePasswordModal').classList.remove('hidden')" class="text-gray-400 hover:text-white p-1" title="Change Password">
                            <i class="fas fa-key"></i>
                        </button>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-white p-1" title="Logout">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    </div>
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

                @if(auth()->check() && auth()->user()->role !== 'Staff')
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

                    @if(auth()->check() && auth()->user()->role === 'Super Admin')
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
                    @auth
                    <div class="flex items-center space-x-3">
                        <button type="button" id="pwaInstallBtn" onclick="triggerPwaInstall()" class="px-3 py-1.5 bg-gradient-to-r from-brand-pink to-brand-purple text-white text-xs font-bold rounded-lg transition-all inline-flex items-center gap-1.5 shadow-sm hover:opacity-90 shadow-purple-500/20" title="Install App (PWA)">
                            <i class="fas fa-download text-xs"></i>
                            <span class="hidden sm:inline">Install App</span>
                        </button>
                        <button type="button" onclick="document.getElementById('changePasswordModal').classList.remove('hidden')" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition-colors inline-flex items-center gap-1.5 shadow-sm" title="Change Password">
                            <i class="fas fa-key text-brand-purple"></i>
                            <span class="hidden sm:inline">Change Password</span>
                        </button>
                        <div x-data="{ 
                            open: false, 
                            unreadCount: {{ auth()->check() ? auth()->user()->unreadNotifications->count() : 0 }},
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
                                    @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                        <button onclick="markAllAsRead()" class="text-xs text-brand-blue hover:underline">Mark all as read</button>
                                    @endif
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    @forelse((auth()->check() ? auth()->user()->notifications->take(10) : []) as $notification)
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
                    @endauth
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

        // Global handler to disable submit buttons and show loading spinner on form submission
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    if (e.defaultPrevented) return;

                    var submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                    submitBtns.forEach(function(btn) {
                        if (btn.disabled) return;
                        btn.disabled = true;
                        btn.classList.add('opacity-60', 'cursor-not-allowed', 'pointer-events-none');
                        if (!btn.querySelector('.fa-spinner')) {
                            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> Processing...';
                        }
                    });
                });
            });
        });

        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

    <!-- Change Password Modal (Accessible to All Logged In Users) -->
    <div id="changePasswordModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm {{ (isset($errors) && ($errors->has('current_password') || $errors->has('password'))) ? '' : 'hidden' }} overflow-y-auto h-full w-full z-50 p-4 flex items-center justify-center">
        <div class="relative my-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                    <i class="fas fa-key text-brand-purple mr-2"></i> Change Password
                </h3>
                <button onclick="document.getElementById('changePasswordModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form action="{{ route('password.change') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Current Password *</label>
                    <div class="relative">
                        <input type="password" name="current_password" id="current_password_input" required class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-purple focus:ring-brand-purple pr-10">
                        <button type="button" onclick="togglePasswordVisibility('current_password_input', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">New Password * (Min. 8 characters)</label>
                    <div class="relative">
                        <input type="password" name="password" id="new_password_input" minlength="8" required class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-purple focus:ring-brand-purple pr-10">
                        <button type="button" onclick="togglePasswordVisibility('new_password_input', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Confirm New Password *</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="confirm_password_input" minlength="8" required class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-purple focus:ring-brand-purple pr-10">
                        <button type="button" onclick="togglePasswordVisibility('confirm_password_input', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex justify-end gap-2.5 pt-3 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('changePasswordModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 text-gray-800 text-xs font-semibold rounded-lg hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2 bg-gradient-to-r from-brand-purple to-brand-pink text-white text-xs font-bold rounded-lg hover:opacity-90 shadow-md flex items-center">
                        <i class="fas fa-save mr-1.5"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- PWA Guidance Modal -->
    <div id="pwaInstructionModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative my-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white text-center">
            <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center text-brand-purple text-2xl mx-auto mb-3">
                <i class="fas fa-mobile-alt"></i>
            </div>
            <h3 class="text-lg font-extrabold text-gray-900">Install Loops CRM App</h3>
            <p class="text-xs text-gray-500 mt-1 mb-4">Install this application on your phone, tablet, or desktop computer for instant access.</p>
            
            <div class="bg-gray-50 p-4 rounded-xl text-left space-y-3 text-xs text-gray-700 border border-gray-200">
                <div class="flex items-start gap-2.5">
                    <span class="font-bold text-brand-purple bg-purple-100 px-2 py-0.5 rounded flex-shrink-0">1</span>
                    <div>
                        <strong>Chrome / Edge (Desktop & Mobile):</strong>
                        <p class="text-gray-500 mt-0.5">Click the <i class="fas fa-desktop text-gray-700"></i> <strong>Install App icon</strong> in your browser address bar (top right) or open browser Menu &rarr; <em>Install Loops CRM</em>.</p>
                    </div>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="font-bold text-brand-pink bg-pink-100 px-2 py-0.5 rounded flex-shrink-0">2</span>
                    <div>
                        <strong>Safari (iPhone / iPad / Mac):</strong>
                        <p class="text-gray-500 mt-0.5">Tap the <i class="fas fa-share-alt text-blue-600"></i> <strong>Share button</strong> &rarr; scroll down and choose <em>Add to Home Screen</em>.</p>
                    </div>
                </div>
            </div>

            <button type="button" onclick="document.getElementById('pwaInstructionModal').classList.add('hidden')" class="mt-5 w-full py-2.5 bg-brand-purple text-white text-xs font-bold rounded-xl hover:bg-brand-pink transition-all shadow-md">
                Got It
            </button>
        </div>
    </div>

    <!-- Service Worker & PWA Installation Script -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/serviceworker.js').then(function(registration) {
                    console.log('PWA ServiceWorker registered: ', registration.scope);
                }).catch(function(err) {
                    console.log('PWA ServiceWorker registration failed: ', err);
                });
            });
        }

        let deferredPrompt;
        const pwaBtn = document.getElementById('pwaInstallBtn');

        // Hide install button if app is ALREADY running as an installed standalone app
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
            if (pwaBtn) pwaBtn.style.display = 'none';
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
        });

        async function triggerPwaInstall() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    if (pwaBtn) pwaBtn.style.display = 'none';
                }
                deferredPrompt = null;
            } else {
                const modal = document.getElementById('pwaInstructionModal');
                if (modal) modal.classList.remove('hidden');
            }
        }
    </script>
</body>

</html>