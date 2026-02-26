<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->logAudit('created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $old = array_intersect_key($model->getOriginal(), $model->getDirty());
            $new = $model->getDirty();
            $model->logAudit('updated', $old, $new);
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted', $model->getAttributes(), null);
        });
    }

    public function logAudit($action, $old = null, $new = null)
    {
        // Don't log if no changes (for updates)
        if ($action === 'updated' && empty($new)) {
            return;
        }

        // Determine current user
        $user = null;
        if (Auth::guard('administrator')->check()) {
            $user = Auth::guard('administrator')->user();
        } elseif (Auth::guard('doctor')->check()) {
            $user = Auth::guard('doctor')->user();
        } elseif (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
        }

        AuditLog::create([
            'user_type' => $user ? get_class($user) : null,
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
