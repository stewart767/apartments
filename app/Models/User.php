<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'phone',
        'whatsapp',
        'gender',
        'date_of_birth',
        'avatar',
        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
        ];
    }

    // Role helper methods
    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isReceptionist(): bool
    {
        return $this->role === 'receptionist';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isTenant(): bool
    {
        return $this->role === 'tenant';
    }

    public function isCaretaker(): bool
    {
        return $this->role === 'caretaker';
    }

    public function isAccountant(): bool
    {
        return $this->role === 'accountant';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // Relationships
    public function tenantDetail()
    {
        return $this->hasOne(TenantDetail::class);
    }

    public function ownerDetail()
    {
        return $this->hasOne(OwnerDetail::class);
    }

    public function wizardDrafts()
    {
        return $this->hasMany(WizardDraft::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'tenant_id');
    }

    public function properties()
    {
        return $this->hasMany(Property::class, 'owner_id');
    }

    public function managedProperties()
    {
        return $this->belongsToMany(Property::class, 'property_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'tenant_id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'tenant_id');
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'tenant_id');
    }

    public function assignedMaintenance()
    {
        return $this->hasMany(MaintenanceRequest::class, 'assigned_to');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'tenant_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function tenantAssets()
    {
        return $this->hasMany(TenantAsset::class, 'tenant_id');
    }
}
