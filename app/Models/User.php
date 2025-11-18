<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'business_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is salesperson
     */
    public function isSalesperson()
    {
        return $this->role === 'salesperson';
    }

    /**
     * Check if user is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is inactive
     */
    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    /**
     * Sales made by this user
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Purchases made by this user
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function purchaseHistory()
    {
        return $this->hasMany(PurchaseHistory::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expenses::class);
    }

    /**
     * Product assignments for this user (staff member)
     */
    public function assignments()
    {
        return $this->hasMany(ProductAssignment::class);
    }

    /**
     * Active assignments for this user
     */
    public function activeAssignments()
    {
        return $this->assignments()->whereIn('status', ['assigned', 'in_progress']);
    }

    /**
     * Business this user belongs to
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
