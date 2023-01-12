<?php

namespace KieranFYI\Roles\Core\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;
use KieranFYI\Roles\Core\Services\Register\RegisterPermission;

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

    public static function register(): void
    {
        RegisterPermission::policy(static::class);
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
     * @param array $methods
     * @return array
     */
    public function methods(array $methods = []): array
    {
        if (empty($methods)) {
            $methods = get_class_methods($this);
        }

        $blacklist = array_merge(
            $this->blacklist(),
            [
                '__construct', 'policyName', 'blacklist', 'permissions',
                'allow', 'deny', 'denyWithStatus', 'denyAsNotFound',
                'hasPermission', 'register', 'methods'
            ]
        );

        return array_diff($methods, $blacklist);
    }

    /**
     * @param array $methods
     * @return array
     */
    public function permissions(array $methods = []): array
    {
        return collect($this->methods($methods))
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
    protected function hasPermission(mixed $user, string $prefix): bool
    {
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission($prefix . ' ' . $this->policyName());
        }
        return false;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param mixed $user
     * @return bool
     */
    public function viewAny(mixed $user): bool
    {
        return $this->hasPermission($user, 'View Any');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param mixed $user
     * @param Model $model
     * @return bool
     */
    public function view(mixed $user, Model $model): bool
    {
        return $this->hasPermission($user, 'View');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param mixed $user
     * @return bool
     */
    public function create(mixed $user): bool
    {
        return $this->hasPermission($user, 'Create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param mixed $user
     * @param Model $model
     * @return bool
     */
    public function update(mixed $user, Model $model): bool
    {
        return $this->hasPermission($user, 'Update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param mixed $user
     * @param Model $model
     * @return bool
     */
    public function delete(mixed $user, Model $model): bool
    {
        return $this->hasPermission($user, 'Delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param mixed $user
     * @param Model $model
     * @return bool
     */
    public function restore(mixed $user, Model $model): bool
    {
        return $this->hasPermission($user, 'Restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param mixed $user
     * @param Model $model
     * @return bool
     */
    public function forceDelete(mixed $user, Model $model): bool
    {
        return $this->hasPermission($user, 'Force Delete');
    }
}
