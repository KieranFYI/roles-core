<?php

namespace KieranFYI\Roles\Core\Traits\Roles;

use Illuminate\Database\Eloquent\Model;
use KieranFYI\Roles\Core\Models\Roles\Role;

/**
 * @mixin Model
 */
trait ApplyDefaultRolesTrait
{

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    public static function bootApplyDefaultRolesTrait()
    {
        static::created(function (Model $model) {
            if (!is_object($model) || !in_array(HasRolesTrait::class, class_uses_recursive($model))) {
                return;
            }
            /** @var HasRolesTrait $model */
            Role::where('default', true)
                ->get()
                ->each(function (Role $role) use ($model) {
                    $model->addRole($role);
                });
        });
    }

}