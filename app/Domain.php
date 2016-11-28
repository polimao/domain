<?php

namespace Domain;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected static $unguarded = true;

    public function body()
    {
        return $this->belongsTo('Domain\Body');
    }
    public function suffix()
    {
        return $this->belongsTo('Domain\Suffix');
    }
}
