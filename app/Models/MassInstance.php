<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MassInstance extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['starts_at','capacity','occupied','status','source','priest_id','is_special','special_category','notes','special_details','reservation_amount'];
    // Store and read as app-local time (config('app.timezone'))
    protected $casts = ['starts_at'=>'datetime','is_special'=>'boolean','special_details'=>'array','reservation_amount'=>'decimal:2'];
    public function intentions(): HasMany { return $this->hasMany(Intention::class,'mass_instance_id'); }
    public function priest() { return $this->belongsTo(Priest::class); }
    public function histories() { return $this->hasMany(\App\Models\MassInstanceHistory::class, 'mass_instance_id'); }
    public function scopeAvailable($q){ return $q->whereColumn('occupied','<','capacity')->where('status','scheduled'); }
    public function scopeOrdinary($q){ return $q->where('is_special', false); }
    public function scopeSpecial($q){ return $q->where('is_special', true); }
}
