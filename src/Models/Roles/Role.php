<?php

namespace KieranFYI\Roles\Models\Roles;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use KieranFYI\Logging\Traits\LoggingTrait;
use KieranFYI\Roles\Traits\Permissions\ForcePermissionsTrait;
use KieranFYI\Roles\Traits\Permissions\HasPermissionsTrait;

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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'display_order', 'colour', 'default'
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'permissions'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'default' => 'boolean'
    ];

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withTimestamps();
    }

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
