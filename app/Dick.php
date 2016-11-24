<?php

namespace Domain;

use Illuminate\Database\Eloquent\Model;

class Dick extends Model
{
    protected static $unguarded = true;
    public function saveDick()
    {

    }

    public function body()
    {
        return $this->belongsTo('Domain\Body');
    }

}
