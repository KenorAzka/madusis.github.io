<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'buyer_level',
        'is_debt_allowed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'buyer_level' => 'integer',
            'is_debt_allowed' => 'boolean',
        ];
    }

    /**
     * Relasi ke transaksi/order pelanggan
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /**
     * Accessor dinamis untuk buyer_level
     */
    public function getBuyerLevelAttribute()
    {
        // Hitung total akumulasi belanja dan jumlah transaksi pelanggan
        $totalSpent = $this->orders()->sum('total_amount');
        $orderCount = $this->orders()->count();

        // Syarat Level 2: Minimal 5 kali transaksi ATAU total belanja >= Rp 200.000
        if ($orderCount >= 5 || $totalSpent >= 200000) {
            return 2;
        }
        return 1;
    }

    /**
     * Helper cek izin utang
     */
    public function canUseDebt()
    {
        return $this->buyer_level >= 2;
    }
}