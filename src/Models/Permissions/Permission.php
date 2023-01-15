<?php

namespace KieranFYI\Roles\Core\Models\Permissions;

use Illuminate\Database\Eloquent\Model;
use KieranFYI\Logging\Traits\LoggingTrait;
use KieranFYI\Roles\Core\Traits\BuildsAccess;
use KieranFYI\Services\Core\Traits\Serviceable;

/**
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $group
 * @property integer $power
 */
class Permission extends Model
{
    use LoggingTrait;
    use BuildsAccess;
    use Serviceable;

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
