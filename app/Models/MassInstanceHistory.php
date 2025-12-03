<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MassInstanceHistory extends Model
{
    use HasFactory;
    protected $fillable = ['mass_instance_id','user_id','action','justification','changes'];
    protected $casts = ['changes' => 'array'];

    public function mass(): BelongsTo { return $this->belongsTo(MassInstance::class, 'mass_instance_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
