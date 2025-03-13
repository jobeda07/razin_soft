<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $guarded=['id'];

    protected $casts = [
        'assigned_user_ids' => 'array',
    ];

    public function createdBy(){
        return $this->belongsTo(User::class,'created_by','id');
    }
}
