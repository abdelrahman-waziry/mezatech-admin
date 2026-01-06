<?php

namespace App\Enums\Analytics;

enum EventName: string
{
    case TRADEIN_STARTED = 'tradein_started';
    case TRADEIN_COMPLETED = 'tradein_completed';
    case REQUOTE_REQUESTED = 'requote_requested';
    case QUOTE_VIEWED = 'quote_viewed';
}
