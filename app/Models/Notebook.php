<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notebook extends Model
{
    use HasFactory;
    use Traits\UseUuid;

    protected $fillable = [
        'id',
        'creator_uuid',
        'name',
        'company',
        'phone',
        'email',
        'birthday',
        'picture',
    ];
}
