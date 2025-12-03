<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MassTimeTemplate extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['dow','time','capacity','active'];
    protected $casts = ['active'=>'boolean'];
}
