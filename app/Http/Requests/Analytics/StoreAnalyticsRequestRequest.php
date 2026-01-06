<?php

namespace App\Http\Requests\Analytics;

use App\Enums\Analytics\AppSource;
use App\Enums\Analytics\ErrorType;
use App\Enums\Analytics\Method;
use App\Enums\Analytics\Network;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreAnalyticsRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_id' => ['required', 'uuid'],
            'endpoint' => ['required', 'string'],
            'method' => ['required', new Enum(Method::class)],
            'timestamp' => ['required', 'date_format:Y-m-d\TH:i:sP'], // ISO-8601
            'app_source' => ['required', new Enum(AppSource::class)],
            'app_version' => ['required', 'string'],
            'device.os' => ['required', 'string'],
            'device.model' => ['required', 'string'],
            'device.network' => ['required', new Enum(Network::class)],
            'response.status' => ['required', 'integer'],
            'response.duration_ms' => ['required', 'integer'],
            'response.error_type' => ['nullable', new Enum(ErrorType::class)],
        ];
    }
}
