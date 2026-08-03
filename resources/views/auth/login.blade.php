<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Invoice System</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="min-h-screen min-h-[100dvh] flex items-center justify-center p-4 sm:p-6 bg-fixed" style="background: linear-gradient(135deg, #ff0878 0%, #8035ca 35%, #0057be 70%, #2fc9c3 100%);">
    <!-- Animated background shapes for depth -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] rounded-full bg-white/10 blur-3xl animate-pulse"></div>
        <div class="absolute top-[60%] -right-[5%] w-[30%] h-[30%] rounded-full bg-white/10 blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="bg-white/95 backdrop-blur-md p-6 sm:p-8 md:p-10 rounded-2xl shadow-2xl w-full max-w-md border border-white/20 transform transition-all duration-300 hover:shadow-white/10 my-auto">
        <div class="flex flex-col items-center mb-6 sm:mb-10">
            <div class="bg-white p-3 sm:p-4 rounded-2xl shadow-sm mb-4 sm:mb-6">
                <img src="{{ asset('images/logo_loops.png') }}" alt="Loops Integrated" class="h-12 sm:h-16 w-auto">
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-gray-800 tracking-tight text-center">Welcome Back</h2>
            <p class="text-gray-500 mt-1 sm:mt-2 text-xs sm:text-sm font-medium">Please enter your credentials</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 sm:mb-8 rounded-r-xl shadow-sm animate-shake">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 font-bold">
                            {{ $errors->first() }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-5 sm:space-y-8" x-data="{ showPassword: false }">
            @csrf
            <div class="space-y-1.5 sm:space-y-2">
                <label for="email" class="block text-xs sm:text-sm font-bold text-gray-700 ml-1">Email Address</label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 group-focus-within:text-blue-600 transition-colors">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" name="email" id="email" required
                        placeholder="name@company.com"
                        class="block w-full pl-11 pr-4 py-3.5 sm:py-4 bg-gray-50 border-2 border-transparent rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all duration-300 text-gray-700 text-base sm:text-sm font-medium">
                </div>
            </div>

            <div class="space-y-1.5 sm:space-y-2">
                <label for="password" class="block text-xs sm:text-sm font-bold text-gray-700 ml-1">Password</label>
                <div class="relative group" x-data="{ focused: false }">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400" :class="focused ? 'text-blue-600' : ''">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required
                        @focus="focused = true" @blur="focused = false"
                        placeholder="••••••••"
                        class="block w-full pl-11 pr-12 py-3.5 sm:py-4 bg-gray-50 border-2 border-transparent rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all duration-300 text-gray-700 text-base sm:text-sm font-medium">
                    
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-blue-600 transition-colors focus:outline-none">
                        <i class="fas fa-lg" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full flex justify-center items-center py-3.5 sm:py-4 px-6 border border-transparent rounded-xl shadow-xl text-base sm:text-lg font-black text-white bg-blue-600 hover:bg-blue-700 hover:shadow-blue-500/25 focus:outline-none focus:ring-4 focus:ring-blue-200 transition-all duration-300 transform active:scale-95">
                    Sign In <i class="fas fa-arrow-right ml-2 text-sm"></i>
                </button>
            </div>
        </form>

        <div class="mt-6 sm:mt-8 text-center">
            <p class="text-xs text-gray-400 font-medium uppercase tracking-widest">&copy; {{ date('Y') }} Loops Integrated. All rights reserved.</p>
        </div>
    </div>

    <style>
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .animate-shake { animation: shake 0.4s ease-in-out; }
    </style>
</body>

</html>