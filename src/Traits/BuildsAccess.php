<?php

namespace KieranFYI\Roles\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Gate;

trait BuildsAccess
{
    use AuthorizesRequests;
    use ValidatesRequests;

    /**
     * @param Model $model
     * @return void
     */
    protected function buildAccess(Model $model): void
    {
        $access = [];
        foreach ($this->resourceAbilityMap() as $method => $policy) {
            if (in_array($method, $this->resourceMethodsWithoutModels())) {
                continue;
            }
            $access[$method] = Gate::any($policy, $model);
        }
        $model->setAttribute('access', $access);
    }
}