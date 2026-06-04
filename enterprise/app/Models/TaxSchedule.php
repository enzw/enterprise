<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxSchedule extends Model
{
    protected $fillable = ['name', 'rate', 'is_taxable', 'is_active'];
}
