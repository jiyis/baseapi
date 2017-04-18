<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Traits\PresentableTrait;

class BaseModel extends Model
{

    protected $guarded = ['id', 'updated_at'];

    protected $hidden = ['updated_at', 'extra'];

}
