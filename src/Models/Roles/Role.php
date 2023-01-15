<?php

namespace KieranFYI\Roles\Core\Models\Roles;

use Illuminate\Database\Eloquent\Model;
use KieranFYI\Logging\Traits\LoggingTrait;
use KieranFYI\Roles\Core\Traits\BuildsAccess;
use KieranFYI\Roles\Core\Traits\Permissions\ForcePermissionsTrait;
use KieranFYI\Roles\Core\Traits\Permissions\HasPermissionsTrait;
use KieranFYI\Services\Core\Eloquent\Builder;
use KieranFYI\Services\Core\Traits\Serviceable;

/**
 * @property integer $id
 * @property string $name
 * @property string $colour
 * @property integer $display_order
 * @property boolean $default
 */
class Role extends Model
{
    use HasPermissionsTrait;
    use ForcePermissionsTrait;
    use LoggingTrait;
    use BuildsAccess;
    use Serviceable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'display_order', 'colour', 'default'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'default' => 'boolean'
    ];

    /**
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDisplayNameHtmlAttribute(): string
    {
        return $this->displayNameHtml();
    }

    /**
     * @param string|null $name
     * @return string
     */
    public function displayNameHtml(string $name = null): string
    {
        $name = $name ?? $this->name;
        if (empty($this->colour)) {
            return e($name);
        }

        return '<span style="color: ' . $this->colour . ';font-weight: 600;">' . e($name) . '</span>';
    }
}
