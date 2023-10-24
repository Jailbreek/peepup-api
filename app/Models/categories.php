<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class categories extends Model {
    protected $table = 'categories';
    protected $primaryKey = "id";
    protected $keyType = "int";
    protected $fillable = ["label"];
    protected $incrementing = true;
    protected $timestamps = false;
}
