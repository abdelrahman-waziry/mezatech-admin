<?php

namespace App\Http\Requests\Analytics;

use App\Enums\Analytics\Condition;
use App\Enums\Analytics\EventName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreAnalyticsEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_name' => ['required', new Enum(EventName::class)],
            'timestamp' => ['required', 'date_format:Y-m-d\TH:i:sP'], // ISO-8601
            'user_id' => ['nullable', 'string'],
            'context.brand' => ['required_if:event_name,tradein_started,tradein_completed,requote_requested,quote_viewed', 'string'],
            'context.model' => ['required_if:event_name,tradein_started,tradein_completed,requote_requested,quote_viewed', 'string'],
            'context.condition' => ['required_if:event_name,tradein_completed', new Enum(Condition::class)],
            'context.quoted_price' => ['nullable', 'numeric'],
            'location.country' => ['required', 'string'],
            'location.city' => ['required', 'string'],
            'location.area' => ['nullable', 'string'],
            'location.district' => ['nullable', 'string'],
            'device.brand' => ['required', 'string'],
            'device.model' => ['required', 'string'],
            'device.os_version' => ['required', 'string'],
        ];
    }
}
