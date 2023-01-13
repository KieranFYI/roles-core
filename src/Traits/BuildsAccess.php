<?php

namespace KieranFYI\Roles\Core\Traits;

use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

/**
 * @mixin Model
 */
trait BuildsAccess
{

    /**
     * @var array
     */
    private static ?array $methods = null;

    /**
     * @var string|null
     */
    private static mixed $type = null;

    /**
     * @var mixed|array
     */
    private static ?array $abilities = null;

    /**
     * @return array
     * @throws ReflectionException
     */
    private static function abilities(): array
    {
        if (is_null(self::$abilities)) {
            if (is_null(request()->route()) || is_null(request()->route()->controller)) {
                return self::$abilities = [];
            }
            $controller = request()->route()->controller;

            if (!in_array(AuthorizesRequests::class, class_uses_recursive($controller))) {
                return self::$abilities = [];
            }

            $abilities = new ReflectionMethod($controller, 'resourceAbilityMap');
            $abilities->setAccessible(true);
            self::$abilities = $abilities->invoke($controller);
        }

        return self::$abilities;
    }

    /**
     * @return mixed
     * @throws ReflectionException
     */
    private static function type(): mixed {
        if (is_null(self::$type)) {
            $abilities = self::abilities();
            if (empty($abilities) || !isset($abilities[request()->route()->getActionMethod()])) {
                return self::$type = false;
            }

            $controller = request()->route()->controller;
            $action = $abilities[request()->route()->getActionMethod()];
            $params = collect(request()->route()->signatureParameters(UrlRoutable::class));
            $middleware = collect($controller->getMiddleware())
                ->pluck('middleware')
                ->firstWhere(function (string $middleware) use ($action) {
                    return str_starts_with($middleware, 'can:' . $action . ',');
                });
            $param = substr($middleware, strrpos($middleware, ',') + 1);

            $type = $params->mapWithKeys(function (ReflectionParameter $value, $key) use ($param) {
                if ($value->getName() !== $param) {
                    return null;
                }
               return [$value->getName() => $value->getType()->getName()];
            })->get($param);

            self::$type = $type ?? $param;
        }

        return self::$type;
    }

    /**
     * @throws ReflectionException
     */
    public function initializeBuildsAccess(): void
    {
       if (!is_a(self::type(), $this::class, true)) {
            return;
        }
        array_push($this->appends, 'access');
    }

    /**
     * @var array
     */
    private array $permissions = [];

    /**
     * @return array
     * @throws ReflectionException
     */
    protected function getAccessAttribute(): array
    {
        if (empty($this->permissions)) {
            /** @var Model $this */
            $this->buildAccess($this);
        }
        return $this->permissions;
    }

    /**
     * @param $value
     * @return void
     */
    public function setAccessAttribute($value): void
    {
        if (!empty($this->permissions)) {
            return;
        }
        $this->permissions = $value;
    }

    /**
     * @param Model $model
     * @return void
     * @throws ReflectionException
     */
    protected function buildAccess(Model $model): void
    {
        if (!is_a($model, self::type(), true)) {
            return;
        }

        $access = [];
        foreach (self::abilities() as $method => $policy) {
            $access[$method] = Gate::any($policy, $model);
        }
        $model->setAttribute('access', $access);
        $model->setAppends(array_merge(['access'], $model->getAppends()));
    }
}