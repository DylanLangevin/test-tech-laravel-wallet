<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Notifications\BalanceLow;

class Wallet extends Model
{
    use HasFactory;

    public const NOTIFIABLE_LIMIT = 10;

    protected $fillable = [
        'user_id',
        'balance'
    ];

    protected static function booted()
    {
        static::updated(function (Wallet $wallet) {

            $wallet->checkBalance();
        });
    }

    /**
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<WalletTransaction>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    // /**
    //  * @return 
    //  */
    public function checkBalance()
    {
        if (!$this || !$this->balance) {
            return;
        }

        if (($this->balance/100) < self::NOTIFIABLE_LIMIT) {

            $user = $this->user; 

            if ($user) {
                $user->notify(new BalanceLow());
            }
        }
    }
}
