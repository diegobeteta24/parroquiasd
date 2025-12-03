<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntentionHistory extends Model
{
    use HasFactory;
    protected $fillable = ['intention_id','user_id','action','justification','changes'];
    protected $casts = ['changes' => 'array'];

    public function intention(): BelongsTo { return $this->belongsTo(Intention::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
