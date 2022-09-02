<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Notebook
 *
 * @property string $id
 * @property string $creator_uuid
 * @property string $name
 * @property string|null $company
 * @property string $phone
 * @property string $email
 * @property string|null $birthday
 * @property string|null $picture
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string|null $picture_url
 * @method static \Database\Factories\NotebookFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Notebook newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notebook newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notebook query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notebook whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notebook whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notebook whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notebook whereCreatorUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notebook whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notebook whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notebook whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notebook wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notebook wherePicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notebook whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
