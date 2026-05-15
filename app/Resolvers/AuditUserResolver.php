<?php

namespace App\Resolvers;

use App\Models\User;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\UserResolver;

class AuditUserResolver implements UserResolver
{
    public static function resolve(Auditable $auditable = null)
    {
        if (auth()->check()) {
            return auth()->user();
        }
        return null;
    }
}