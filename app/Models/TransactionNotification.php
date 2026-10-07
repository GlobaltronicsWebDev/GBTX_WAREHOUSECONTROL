<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionNotification extends Model
{
    protected $fillable = [
        'user_id',
        'actor_id',
        'actor_name',
        'actor_role',
        'title',
        'message',
        'type',
        'reference_id',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public static function log(
        string $title,
        string $message,
        string $type = 'general',
        ?string $referenceId = null,
        ?User $actor = null,
        ?int $targetUserId = null
    ): self {
        $actor = $actor ?? auth()->user();

        return self::create([
            'user_id' => $targetUserId,
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name ?? 'System',
            'actor_role' => $actor?->roles->first()?->name ?? 'System',
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'reference_id' => $referenceId,
            'is_read' => false,
        ]);
    }
}
