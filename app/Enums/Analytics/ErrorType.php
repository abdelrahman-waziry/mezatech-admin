<?php

namespace App\Enums\Analytics;

enum ErrorType: string
{
    case VALIDATION_ERROR = 'validation_error';
    case AUTH_ERROR = 'auth_error';
    case SERVER_ERROR = 'server_error';
}
