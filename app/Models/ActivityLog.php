<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'subject_type', 'subject_id', 'action',
        'description', 'changes', 'performed_by',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * Log an action performed by a super admin (or the system).
     */
    public static function log(string $action, string $description, mixed $subject = null, array $changes = []): void
    {
        static::create([
            // DESIGN-07 Fix: نحفظ الاسم الكامل للكلاس بدلاً من class_basename
            // هذا يتوافق مع اتفاقية Eloquent Polymorphic ويُتيح whereSubjectType(Model::class)
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->id,
            'action'       => $action,
            'description'  => $description,
            'changes'      => $changes ?: null,
            'performed_by' => auth('super_admin')->user()?->name ?? 'System',
        ]);
    }
}
