<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    const CREATED_AT = 'created';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'updated';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','profile_id','organization_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_by_user_id', 'created_by_user_id', 'updated', 'last_login_ip', 'login_num', 'last_login_time'
    ];
}
