<?php

namespace KieranFYI\Roles\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

abstract class AbstractPolicy
{
    use HandlesAuthorization;

    /**
     * @var string
     */
    private string $policyName;

    /**
     * @var array
     */
    protected array $blacklist = [];

    public function __construct()
    {
        $name = str_replace('Policy', '', substr(strrchr(static::class, '\\'), 1));
        $this->policyName = trim(ucwords(implode(' ', preg_split('/(?=[A-Z])/', $name))));
    }

    /**
     * @return string
     */
    public function policyName(): string
    {
        return $this->policyName;
    }

    /**
     * @return array
     */
    public function blacklist(): array
    {
        return $this->blacklist;
    }

    /**
     * @return array
     */
    public function permissions($methods = []): array
    {
        if (empty($methods)) {
            $methods = get_class_methods($this);
        }

        $blacklist = array_merge(
            $this->blacklist(),
            [
                '__construct', 'policyName', 'blacklist', 'permissions',
                'allow', 'deny', 'denyWithStatus', 'denyAsNotFound'
            ]
        );

        return collect($methods)
            ->diff($blacklist)
            ->map(function (string $method) {
                $method = trim(ucwords(implode(' ', preg_split('/(?=[A-Z])/', $method))));
                $description = '';
                return [
                    'name' => $method . ' ' . $this->policyName(),
                    'description' => $description,
                    'group' => $this->policyName(),
                ];
            })
            ->filter()
            ->toArray();
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission('View Any ' . $this->policyName());
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Model $model
     * @return bool
     */
    public function view(User $user, Model $model): bool
    {
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission('View ' . $this->policyName());
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission('Create ' . $this->policyName());
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Model $model
     * @return bool
     */
    public function update(User $user, Model $model): bool
    {
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission('Update ' . $this->policyName());
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Model $model
     * @return bool
     */
    public function delete(User $user, Model $model): bool
    {
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission('Delete ' . $this->policyName());
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Model $model
     * @return bool
     */
    public function restore(User $user, Model $model): bool
    {
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission('Restore ' . $this->policyName());
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Model $model
     * @return bool
     */
    public function forceDelete(User $user, Model $model): bool
    {
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission('Force Delete ' . $this->policyName());
        }
        return false;
    }

}