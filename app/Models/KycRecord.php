<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'bvn_encrypted', 'nin_encrypted', 'id_type',
        'id_number_encrypted', 'selfie_url', 'id_document_url',
        'dojah_verification_id', 'status', 'rejection_reason',
        'business_name', 'business_type', 'rc_number',
        'cac_document_url', 'tax_id_encrypted',
        'submitted_at', 'verified_at',
    ];

    protected $hidden = [
        'bvn_encrypted', 'nin_encrypted', 'id_number_encrypted', 'tax_id_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
