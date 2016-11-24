<?php

namespace Domain;

use Illuminate\Database\Eloquent\Model;

class Body extends Model
{
    protected static $unguarded = true;

    public function docks()
    {
        return $this->hasMany('Domain\Dock');
    }
}
