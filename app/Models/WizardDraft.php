<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WizardDraft extends Model
{
    protected $fillable = [
        'user_id',
        'wizard_type',
        'current_step',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
