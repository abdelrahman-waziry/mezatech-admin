<?php

namespace App\Enums\Analytics;

enum Network: string
{
    case WIFI = 'wifi';
    case CELLULAR = 'cellular';
    case UNKNOWN = 'unknown';
}
