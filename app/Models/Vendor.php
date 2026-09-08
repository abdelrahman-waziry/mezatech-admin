<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Client;

class Vendor extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'company_name',
        'contact_name',
        'email',
        'password',
        'status',
        'embed_key',
        'allowed_origins',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'embed_key',
    ];

    protected $casts = [
        'password' => 'hashed',
        'allowed_origins' => 'array',
    ];

    /**
     * OAuth clients issued to this vendor (Passport's polymorphic "owner").
     */
    public function oauthClients(): MorphMany
    {
        return $this->morphMany(Client::class, 'owner');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Whether a request Origin/Referer host is permitted for this vendor's embed key.
     * No allow-list configured means any origin is allowed.
     */
    public function originAllowed(?string $origin): bool
    {
        if (empty($this->allowed_origins)) {
            return true;
        }

        if (! $origin) {
            return false;
        }

        $host = parse_url($origin, PHP_URL_HOST) ?? $origin;

        return in_array($host, $this->allowed_origins, true);
    }
}
