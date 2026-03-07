<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            static::logAudit($model, 'CREATE');
        });

        static::updated(function ($model) {
            static::logAudit($model, 'UPDATE');
        });

        static::deleted(function ($model) {
            static::logAudit($model, 'DELETE');
        });
    }

    protected static function logAudit($model, $action)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'old_values' => $action === 'UPDATE' ? $model->getOriginal() : null,
            'new_values' => $action !== 'DELETE' ? $model->getAttributes() : null,
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
