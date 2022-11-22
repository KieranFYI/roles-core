<?php

namespace KieranFYI\Roles\Models\Permissions;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $group
 * @property integer $power
 */
class Permission extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'power', 'group',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'power' => 'integer'
    ];
}
