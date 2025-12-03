<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Image;
use App\Models\IntentionHistory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Intention extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'mass_instance_id',
        'type',
        'category',
        'public_text',
        'donor_name',
        'phone',
        'email',
        'amount',
        'stipend_amount_gtq',
        'payment_method',
        'payment_ref',
        'status',
        'paid_at',
        'channel',
        'is_prepaid',
        'hold_expires_at',
        'code',
        'group_code',
        'payment_intent_id',
        'amount_in_cents',
        'currency',
        'metadata',
        'certificate_token',
        'certificate_path',
        'certificate_generated_at',
    ];
    protected $casts = [
        'hold_expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'is_prepaid' => 'boolean',
        'metadata' => 'array',
        'certificate_generated_at' => 'datetime',
    ];
    public function mass(): BelongsTo { return $this->belongsTo(MassInstance::class,'mass_instance_id'); }
    public function dedicatees(): HasMany { return $this->hasMany(IntentionDedicatee::class); }
    public function dedicatee(): HasOne { return $this->hasOne(IntentionDedicatee::class); }
    public function receipt(): MorphOne { return $this->morphOne(Image::class, 'imageable')->where('collection','receipt'); }
    public function histories(): HasMany { return $this->hasMany(IntentionHistory::class); }

    // Helpers
    public function scopeSiblings($query, Intention $self)
    {
        if ($self->group_code) {
            return $query->where('group_code', $self->group_code);
        }
        // Fallback heuristic: same donor/type/public_text code prefix (first 4 hex) and channel
        return $query->where('type', $self->type)
            ->where('donor_name', $self->donor_name)
            ->where('public_text', $self->public_text)
            ->where('channel', $self->channel);
    }

    public function getCertificateUrlAttribute(): ?string
    {
        if (!$this->certificate_token) {
            return null;
        }

        return route('intentions.certificate.public', [$this, $this->certificate_token]);
    }
}
