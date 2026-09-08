@extends('layouts.vendor')

@section('title', 'Register')

@section('content')
<div class="max-w-md mx-auto animate-fade-in-up">
    <div class="text-center mb-8">
        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 mb-2">
            Create your <span class="text-gradient">vendor account</span>
        </h1>
        <p class="text-muted-foreground">Get API access to MezaTech's pricing engine in minutes.</p>
    </div>

    <div class="bg-white rounded-2xl border border-border shadow-elevated p-8">
        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('vendor.register') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Company name</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" required
                    class="w-full rounded-lg border border-border px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact name</label>
                <input type="text" name="contact_name" value="{{ old('contact_name') }}"
                    class="w-full rounded-lg border border-border px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full rounded-lg border border-border px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary transition">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <input type="password" name="password" required
                        class="w-full rounded-lg border border-border px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full rounded-lg border border-border px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary transition">
                </div>
            </div>

            <button type="submit"
                class="w-full inline-flex items-center justify-center gap-2 h-12 px-6 font-medium rounded-lg bg-gradient-primary text-black shadow-soft hover:shadow-glow hover:scale-[1.01] active:scale-[0.99] transition-all duration-200">
                Create account
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M5 12h14M12 5l7 7-7 7"></path>
                </svg>
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-muted-foreground">
            Already have an account? <a href="{{ route('vendor.login') }}" class="font-medium text-gray-900 hover:text-primary transition-colors">Log in</a>
        </p>
    </div>
</div>
@endsection
