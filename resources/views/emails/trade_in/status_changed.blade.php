<x-mail::message>
# Trade In Request Updated

Your trade-in request #{{ $tradeInRequest->id }} status has been updated to **{{ ucfirst($tradeInRequest->status) }}**.

@if($tradeInRequest->admin_comment)
**Admin Comment:**
{{ $tradeInRequest->admin_comment }}
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
