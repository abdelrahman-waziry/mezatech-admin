<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vendor Portal') · MezaTech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#00FB02',
                            foreground: '#000000',
                        },
                        accent: '#111827',
                        muted: {
                            DEFAULT: 'hsl(0, 0%, 96%)',
                            foreground: 'hsl(0, 0%, 46%)',
                        },
                        border: 'hsl(0, 0%, 91%)',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <style>
        .text-gradient {
            background: linear-gradient(135deg, #00FB02 0%, #00a301 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .bg-gradient-primary {
            background: linear-gradient(135deg, #00FB02 0%, #00a301 100%);
        }
        .bg-gradient-hero {
            background: linear-gradient(180deg, hsl(0, 0%, 98%) 0%, hsl(0, 0%, 95%) 100%);
        }
        .shadow-soft {
            box-shadow: 0 4px 24px -4px rgba(0, 0, 0, 0.08);
        }
        .shadow-elevated {
            box-shadow: 0 12px 40px -8px rgba(0, 0, 0, 0.12);
        }
        .shadow-glow {
            box-shadow: 0 0 60px -12px rgba(0, 251, 2, 0.4);
        }
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-fade-in-up {
            animation: fade-in-up 0.6s ease-out forwards;
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
    </style>
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gradient-hero min-h-screen">

    <header class="fixed top-4 left-0 right-0 z-50 flex justify-center px-4">
        <nav class="flex items-center justify-between gap-12 py-4 px-8 rounded-[60px] backdrop-blur-[2px] bg-[#000000b0] shadow-[0_0_20px_#0000001a,0_6px_6px_#0003] w-full max-w-5xl">
            <a href="{{ auth('vendor')->check() ? route('vendor.dashboard') : url('/') }}" class="flex items-center gap-3 group">
                <img src="https://cdn.prod.website-files.com/68ae41dbb934b457bd54efb0/68d5073ad8915b78375a0937_Logo%20Meza%20Tech.svg"
                     alt="MezaTech Logo"
                     class="h-7 w-auto group-hover:scale-105 transition-transform duration-200">
                <span class="text-white/60 text-sm font-medium hidden sm:inline">Vendor Portal</span>
            </a>

            <div class="flex items-center gap-4">
                @auth('vendor')
                    <span class="text-sm text-gray-300 hidden sm:inline">{{ auth('vendor')->user()->company_name }}</span>
                    <form method="POST" action="{{ route('vendor.logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">
                            Log out
                        </button>
                    </form>
                @else
                    <a href="{{ route('vendor.login') }}" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">
                        Log in
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary/5 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-accent/5 rounded-full blur-3xl animate-float" style="animation-delay: -3s;"></div>
    </div>

    <main class="relative z-10 pt-32 pb-16 px-4">
        <div class="max-w-5xl mx-auto">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-primary/30 bg-primary/5 px-4 py-3 text-sm text-gray-900">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>
    @stack('scripts')
</body>
</html>
