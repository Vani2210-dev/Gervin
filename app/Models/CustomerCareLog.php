<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCareLog extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'visit_date',
        'status',
        'feedback',
        'customer_proposal',
        'sale_proposal',
        'photos',
        'latitude',
        'longitude',
        'notes',
    ];

    protected $casts = [
        'photos' => 'array',
        'visit_date' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
