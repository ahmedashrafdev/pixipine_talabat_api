<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $guarded =[];
    public function state()
    {
        return $this->belongsTo('App\State');
    }
}
