<?php

namespace KieranFYI\Roles\Core\Policies;

use Auth;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
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

        if (empty($this->policyName)) {
            throw new Exception('Invalid Class Name: ' . static::class);
        }
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
                'allow', 'deny', 'denyWithStatus', 'denyAsNotFound',
                'hasPermission'
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
     * @param string $prefix
     * @return bool
     */
    private function hasPermission(string $prefix): bool
    {
        $user = Auth::user();
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission($prefix . ' ' . $this->policyName());
        }
        return false;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @return bool
     */
    public function viewAny(mixed $m = null): bool
    {
        return $this->hasPermission('View Any');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param Model $model
     * @return bool
     */
    public function view(Model $model): bool
    {
        return $this->hasPermission('View');
    }

    /**
     * Determine whether the user can create models.
     *
     * @return bool
     */
    public function create(): bool
    {
        return $this->hasPermission('Create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param Model $model
     * @return bool
     */
    public function update(Model $model): bool
    {
        return $this->hasPermission('Update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model): bool
    {
        return $this->hasPermission('Delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param Model $model
     * @return bool
     */
    public function restore(Model $model): bool
    {
        return $this->hasPermission('Restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param Model $model
     * @return bool
     */
    public function forceDelete(Model $model): bool
    {
        return $this->hasPermission('Force Delete');
    }

}
