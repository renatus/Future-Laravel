<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    /**
     * The attributes that should be excluded from serialization
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'picture',
    ];

    /**
     * Custom attributes that should be appended to the model
     *
     * @var array<int, string>
     */
    protected $appends = ['picture_url'];

    /**
     * Custom 'virtual' attribute with pic URL
     *
     * 'picture' attribute contains relative file path, like this:
     * images/2022/07/96c37fc7-a3d7-4679-8d72-082ec3f90062.jpg
     * Will generate full URL from it.
     * @return string|null
     */
    public function getPictureUrlAttribute()
    {
        // If there is a picture associated with the model instance
        if ($this->picture) {
            return Storage::url($this->picture);
        }
        return null;
    }
}
