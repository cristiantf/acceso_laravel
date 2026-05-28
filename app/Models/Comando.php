<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comando extends Model
{
    protected $fillable = [
        'company_id',
        'instruccion',
        'estado',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeOfCompany($query, $company = null)
    {
        $company = $company ?? app('company');
        return $query->where('company_id', $company->id);
    }
}
