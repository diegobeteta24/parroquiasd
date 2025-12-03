<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntentionDedicatee extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['intention_id','name','relation'];
    public function intention(): BelongsTo { return $this->belongsTo(Intention::class); }
}
