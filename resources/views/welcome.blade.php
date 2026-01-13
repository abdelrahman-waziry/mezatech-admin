<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MezaTech - Pricing API & Analytics</title>
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
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-fade-in {
            animation: fade-in 0.5s ease-out forwards;
        }
        .animate-fade-in-up {
            animation: fade-in-up 0.6s ease-out forwards;
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gradient-hero min-h-screen">

    <!-- Header -->
    <!-- Header -->
    <header class="fixed top-4 left-0 right-0 z-50 flex justify-center px-4">
        <nav class="flex items-center justify-between gap-12 py-5 px-12 rounded-[60px] backdrop-blur-[2px] bg-[#000000b0] shadow-[0_0_20px_#0000001a,0_6px_6px_#0003] w-full max-w-7xl">
            <!-- Logo & Brand -->
            <a href="/" class="flex items-center gap-3 group">
                <img src="https://cdn.prod.website-files.com/68ae41dbb934b457bd54efb0/68d5073ad8915b78375a0937_Logo%20Meza%20Tech.svg" 
                     alt="MezaTech Logo" 
                     class="h-8 w-auto group-hover:scale-105 transition-transform duration-200">
            </a>
            
            <!-- Auth Links -->
            <div class="flex items-center gap-4">
                <a href="/admin/login" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">
                    Log in
                </a>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center pt-16 overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary/5 rounded-full blur-3xl animate-float"></div>
            <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-accent/5 rounded-full blur-3xl animate-float" style="animation-delay: -3s;"></div>
        </div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-6 py-20 text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 rounded-full border border-border bg-white px-4 py-1.5 text-sm shadow-soft mb-8 animate-fade-in opacity-0" style="animation-delay: 0.1s;">
                <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z"></path>
                </svg>
                <span class="text-muted-foreground">Enterprise-Grade Pricing Engine</span>
            </div>
            
            <!-- Heading -->
            <h1 class="max-w-4xl mx-auto text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold tracking-tight text-gray-900 leading-[1.1] mb-6 animate-fade-in opacity-0" style="animation-delay: 0.2s;">
                Intelligent Pricing &
                <br>
                <span class="text-gradient">Real-time Analytics</span>
            </h1>
            
            <!-- Subheading -->
            <p class="max-w-2xl mx-auto text-lg sm:text-xl text-muted-foreground mb-10 animate-fade-in opacity-0" style="animation-delay: 0.3s;">
                Empower your business with dynamic pricing strategies and deep data insights. Optimize revenue with our high-performance API and analytics dashboard.
            </p>
            
            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 animate-fade-in opacity-0" style="animation-delay: 0.4s;">
                <a href="/admin" class="inline-flex items-center justify-center gap-2 h-14 px-10 text-lg font-medium rounded-lg bg-gradient-primary text-black shadow-soft hover:shadow-glow hover:scale-[1.02] active:scale-[0.98] transition-all duration-200">
                    Get Started
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M5 12h14M12 5l7 7-7 7"></path>
                    </svg>
                </a>
                <a href="/docs" class="inline-flex items-center justify-center h-14 px-10 text-lg font-medium rounded-lg border-2 border-primary/30 bg-transparent text-gray-900 hover:bg-primary/5 hover:border-primary/50 transition-all duration-200">
                    API Documentation
                </a>
            </div>
        </div>
    </section>
</body>
</html>
