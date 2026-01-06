<?php

namespace App\Enums\Analytics;

enum Condition: string
{
    case EXCELLENT = 'excellent';
    case GOOD = 'good';
    case FAIR = 'fair';
    case DAMAGED = 'damaged';
}
