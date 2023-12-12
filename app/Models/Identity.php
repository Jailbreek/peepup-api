<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Identity extends Model
{
    protected $connection = "identity_db_server";
    protected $table = "identities";
}
