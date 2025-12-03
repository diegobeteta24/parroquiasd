<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MassOverride extends Model
{
    use HasFactory;
    protected $fillable = ['date','action','time','capacity','note'];
    protected $casts = ['date'=>'date'];
}
