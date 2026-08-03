<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Maintenance - Loops Integrated</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-fixed" style="background: linear-gradient(135deg, #ff0878 0%, #8035ca 35%, #0057be 70%, #2fc9c3 100%);">
    <!-- Animated background shapes for depth -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] rounded-full bg-white/10 blur-3xl animate-pulse"></div>
        <div class="absolute top-[60%] -right-[5%] w-[30%] h-[30%] rounded-full bg-white/10 blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="bg-white/95 backdrop-blur-md p-10 rounded-2xl shadow-2xl w-full max-w-lg border border-white/20 transform transition-all duration-300 hover:shadow-white/10 text-center relative z-10">
        <div class="flex flex-col items-center mb-8">
            <div class="bg-white p-4 rounded-2xl shadow-sm mb-6">
                <img src="{{ asset('images/logo_loops.png') }}" alt="Loops Integrated" class="h-16 w-auto">
            </div>
            
            <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center text-orange-500 mb-6 animate-bounce">
                <i class="fas fa-tools text-3xl"></i>
            </div>
            
            <h2 class="text-3xl font-black text-gray-800 tracking-tight">System Under Maintenance</h2>
            <p class="text-gray-600 mt-4 font-medium leading-relaxed">
                We are currently performing scheduled system upgrades and maintenance to improve your experience. Active operations are temporarily paused.
            </p>
        </div>

        <div class="border-t border-gray-100 pt-6">
            <p class="text-sm text-gray-500 font-medium">
                Please check back in a few minutes. We apologize for any inconvenience caused.
            </p>
        </div>
        


        <div class="mt-8 text-center border-t border-gray-100 pt-6">
            <p class="text-xs text-gray-400 font-medium uppercase tracking-widest">&copy; {{ date('Y') }} Loops Integrated. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
