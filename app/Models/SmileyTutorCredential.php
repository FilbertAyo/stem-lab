<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmileyTutorCredential extends Model
{
    protected $fillable = [
        'username',
        'password',
        'user_id',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        // Only hide when serializing to array/JSON; we show password in views for user to copy
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Assign an available credential to the given user.
     */
    public static function assignToUser(User $user): ?self
    {
        $credential = self::whereNull('user_id')->first();

        if ($credential) {
            $credential->update(['user_id' => $user->id]);

            return $credential->fresh();
        }

        return null;
    }
}
