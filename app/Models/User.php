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
        'organization_id',
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
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function ownedOrganization()
    {
        return $this->hasOne(Organization::class, 'owner_user_id');
    }

    /**
     * Resolve the active organization for this user (owned or member).
     * Automatically creates a default portfolio organization if none exists for owners.
     */
    public function resolveOrganization(): Organization
    {
        if ($this->organization_id) {
            $org = Organization::find($this->organization_id);
            if ($org) {
                return $org;
            }
        }

        $owned = $this->ownedOrganization;
        if ($owned) {
            if (!$this->organization_id) {
                $this->update(['organization_id' => $owned->id]);
            }
            return $owned;
        }

        // Auto-create default organization for property owner
        $orgName = $this->name ? "{$this->name}'s Properties" : "Portfolio #{$this->id}";
        $slug = \Illuminate\Support\Str::slug($orgName) . '-' . $this->id;

        $org = Organization::create([
            'owner_user_id' => $this->id,
            'name' => $orgName,
            'slug' => $slug,
            'phone' => $this->phone,
            'email' => $this->email,
            'country' => 'Tanzania',
        ]);

        $this->update(['organization_id' => $org->id]);

        // Link existing properties owned by this user
        Property::where('owner_id', $this->id)
            ->whereNull('organization_id')
            ->update(['organization_id' => $org->id]);

        return $org;
    }

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
