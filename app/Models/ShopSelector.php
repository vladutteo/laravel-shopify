<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSelector extends Model {

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'user_id'
    ];

}
