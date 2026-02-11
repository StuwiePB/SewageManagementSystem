<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crew extends Model
{
    protected $fillable = [
        'name',
        'employee_id',
        'role',
        'phone',
        'email',
        'status',
        'hire_date',
        'specialization',
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];
}
