<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public static function log(string $action, string $description, ?string $user = null): AuditLog
    {
        $username = $user ?? (auth('admin')->check() ? auth('admin')->user()->name : (auth('student')->check() ? auth('student')->user()->nama . ' (' . auth('student')->user()->nis . ')' : 'System'));

        return AuditLog::create([
            'user' => $username,
            'action' => $action,
            'description' => $description,
            'ip' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ]);
    }
}
