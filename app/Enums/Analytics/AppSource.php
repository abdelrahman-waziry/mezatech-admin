<?php

namespace App\Enums\Analytics;

enum AppSource: string
{
    case WEB = 'web';
    case IOS = 'ios';
    case ANDROID = 'android';
    case PARTNER = 'partner';
}
