@extends('layouts.vendor')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6 animate-fade-in-up">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ $vendor->company_name }}</h1>
            <p class="text-muted-foreground text-sm mt-1">{{ $vendor->email }}</p>
        </div>
        <span class="inline-flex items-center gap-1.5 rounded-full border border-border bg-white px-4 py-1.5 text-sm shadow-soft">
            <span class="h-2 w-2 rounded-full {{ $vendor->isActive() ? 'bg-primary' : 'bg-red-500' }}"></span>
            <span class="text-muted-foreground">{{ ucfirst($vendor->status) }}</span>
        </span>
    </div>

    @if (session('newClientSecret'))
        <div class="rounded-2xl border border-primary/30 bg-primary/5 p-6 shadow-soft">
            <p class="font-semibold text-gray-900">Save your client secret now — it will not be shown again.</p>
            <div class="mt-3 grid grid-cols-1 sm:grid-cols-[120px_1fr] gap-x-4 gap-y-2 text-sm font-mono">
                <span class="text-muted-foreground">Client ID</span>
                <span class="break-all text-gray-900">{{ session('newClientId') }}</span>
                <span class="text-muted-foreground">Client Secret</span>
                <span class="break-all text-gray-900">{{ session('newClientSecret') }}</span>
            </div>
        </div>
    @endif

    @if (session('newEmbedKey'))
        <div class="rounded-2xl border border-primary/30 bg-primary/5 p-6 shadow-soft">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <p class="font-semibold text-gray-900">Your embed code is ready</p>
                    <p class="mt-1 text-sm text-muted-foreground">Save the client ID now. It will not be shown again after leaving this page.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-primary/15 px-3 py-1 text-xs font-semibold text-gray-900">Generated securely</span>
            </div>

            <div class="mt-5">
                <div class="mb-2 flex items-center justify-between gap-3">
                    <label for="new-embed-key" class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Client ID</label>
                    <button type="button" data-copy-target="new-embed-key" class="text-xs font-semibold text-gray-700 hover:text-black transition-colors">Copy ID</button>
                </div>
                <input id="new-embed-key" readonly value="{{ session('newEmbedKey') }}"
                    class="w-full rounded-lg border border-border bg-white px-3 py-2.5 font-mono text-sm text-gray-900 shadow-sm"
                    onclick="this.select()">
            </div>

            <div class="mt-5">
                <div class="mb-2 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Embed snippet</p>
                        <p class="mt-1 text-xs text-muted-foreground">Paste this code into your website where the calculator should appear.</p>
                    </div>
                    <button type="button" data-copy-target="embed-snippet" class="shrink-0 text-xs font-semibold text-gray-700 hover:text-black transition-colors">Copy snippet</button>
                </div>
                <pre id="embed-snippet" tabindex="0" class="overflow-x-auto rounded-xl border border-gray-800 bg-gray-950 p-4 text-xs leading-6 text-gray-100 shadow-elevated"><code>{{ session('embedSnippet') }}</code></pre>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-border shadow-soft p-6">
        <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
            <h2 class="text-lg font-semibold text-gray-900">API Credentials</h2>
            <form method="POST" action="{{ route('vendor.credentials.store') }}"
                onsubmit="return confirm('Generating new credentials will revoke the current client. Continue?');">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 h-10 px-5 text-sm font-medium rounded-lg bg-gradient-primary text-black shadow-soft hover:shadow-glow hover:scale-[1.02] active:scale-[0.98] transition-all duration-200">
                    Generate new credentials
                </button>
            </form>
        </div>

        @if ($clients->isEmpty())
            <p class="text-sm text-muted-foreground">No active credentials yet. Generate your first client above.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-muted-foreground border-b border-border">
                            <th class="py-2.5 font-medium">Client ID</th>
                            <th class="py-2.5 font-medium">Name</th>
                            <th class="py-2.5 font-medium">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($clients as $client)
                            <tr class="border-b border-border/60 last:border-0">
                                <td class="py-2.5 font-mono text-xs break-all text-gray-700">{{ $client->id }}</td>
                                <td class="py-2.5 text-gray-900">{{ $client->name }}</td>
                                <td class="py-2.5 text-muted-foreground">{{ $client->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-border shadow-soft p-6">
        <div class="flex items-center justify-between mb-2 flex-wrap gap-3">
            <h2 class="text-lg font-semibold text-gray-900">Trade-in widget</h2>
            @php($embedKeyConfirmation = $vendor->embed_key
                ? 'Rotating the embed key will disable the current widget key. Continue?'
                : 'Generate an embed key for your widget?')
            <form id="embed-key-form" method="POST" action="{{ route('vendor.embed-key.store') }}"
                onsubmit="return confirm('{{ addslashes($embedKeyConfirmation) }}');">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 h-10 px-5 text-sm font-medium rounded-lg bg-gradient-primary text-black shadow-soft hover:shadow-glow hover:scale-[1.02] active:scale-[0.98] transition-all duration-200">
                    {{ $vendor->embed_key ? 'Rotate embed key' : 'Generate embed key' }}
                </button>
            </form>
        </div>
        <p class="text-sm text-muted-foreground">Generate a public key for embedding the trade-in calculator on your website. Rotating it invalidates the previous key.</p>
        <label for="allowed_origins" class="mt-5 block text-sm font-medium text-gray-900">Allowed website origins <span class="font-normal text-muted-foreground">(optional)</span></label>
        <input id="allowed_origins" name="allowed_origins" form="embed-key-form"
            placeholder="store.example.com, www.store.example.com"
            class="mt-1 w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-gray-900">
        <p class="mt-1 text-xs text-muted-foreground">Enter hostnames separated by commas. Leave empty to allow any origin.</p>

        <div class="mt-6 border-t border-border pt-5">
            <h3 class="text-sm font-semibold text-gray-900">Widget events</h3>
            <p class="mt-1 text-sm text-muted-foreground">Listen for these browser events to connect the calculator to your checkout, CRM, analytics, or confirmation page.</p>

            <div class="mt-4 grid gap-3 text-sm">
                <div class="rounded-xl border border-border bg-gray-50 p-4">
                    <p class="font-mono font-semibold text-gray-900">onaccepted</p>
                    <p class="mt-1 text-muted-foreground">Fires when the customer accepts the displayed offer.</p>
                    <pre class="mt-3 overflow-x-auto rounded-lg bg-gray-950 p-3 text-xs leading-5 text-gray-100"><code>calculator.addEventListener('onaccepted', (event) =&gt; {
    const { result, payload } = event.detail;
    console.log('Accepted offer:', result.finalPrice);
    console.log('Calculation payload:', payload);
});</code></pre>
                </div>

                <div class="rounded-xl border border-border bg-gray-50 p-4">
                    <p class="font-mono font-semibold text-gray-900">onrejected</p>
                    <p class="mt-1 text-muted-foreground">Fires when the customer declines the displayed offer.</p>
                    <pre class="mt-3 overflow-x-auto rounded-lg bg-gray-950 p-3 text-xs leading-5 text-gray-100"><code>calculator.addEventListener('onrejected', (event) =&gt; {
    const { result } = event.detail;
    console.log('Rejected offer:', result.finalPrice);
});</code></pre>
                </div>
            </div>

            <p class="mt-4 text-xs text-muted-foreground">The event target is the <code class="rounded bg-gray-100 px-1 py-0.5 font-mono text-gray-700">&lt;trade-in-calculator&gt;</code> element. The accepted event includes <code class="rounded bg-gray-100 px-1 py-0.5 font-mono text-gray-700">result</code> and <code class="rounded bg-gray-100 px-1 py-0.5 font-mono text-gray-700">payload</code>; the rejected event includes <code class="rounded bg-gray-100 px-1 py-0.5 font-mono text-gray-700">result</code>.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-border shadow-soft p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">Quick start: requesting an access token</h2>
        <p class="text-sm text-muted-foreground mb-4">Use your client ID and secret to request an access token via the Client Credentials Grant:</p>
        <pre class="bg-gray-950 text-gray-100 text-xs rounded-xl p-4 overflow-x-auto shadow-elevated">curl -X POST {{ url('/oauth/token') }} \
  -d grant_type=client_credentials \
  -d client_id="YOUR_CLIENT_ID" \
  -d client_secret="YOUR_CLIENT_SECRET"</pre>
        <p class="text-sm text-muted-foreground mt-5 mb-4">Then call the API with the returned bearer token:</p>
        <pre class="bg-gray-950 text-gray-100 text-xs rounded-xl p-4 overflow-x-auto shadow-elevated">curl {{ url('/api/v1/vendor/products') }} \
  -H "Authorization: Bearer ACCESS_TOKEN"</pre>
        <p class="text-sm text-muted-foreground mt-5">
            Full endpoint reference available at
            <a href="{{ url('/docs/vendor-api') }}" class="font-medium text-gray-900 hover:text-primary transition-colors">/docs/vendor-api</a>.
        </p>
    </div>
</div>

@push('scripts')
    <script>
        document.querySelectorAll('[data-copy-target]').forEach((button) => {
            button.addEventListener('click', async () => {
                const target = document.getElementById(button.dataset.copyTarget);
                const text = target.tagName === 'INPUT' ? target.value : target.innerText;

                try {
                    await navigator.clipboard.writeText(text);
                    const originalLabel = button.textContent;
                    button.textContent = 'Copied';
                    setTimeout(() => { button.textContent = originalLabel; }, 1600);
                } catch {
                    target.focus();
                }
            });
        });
    </script>
@endpush
@endsection
