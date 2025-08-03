<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Country;

class Company extends Model
{
    protected $fillable = [
        'name',
        'vat_number',
        'address',
        'city',
        'state',
        'zip',
        'country_id'
    ];

    /**
     * Get the country that owns the company.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
