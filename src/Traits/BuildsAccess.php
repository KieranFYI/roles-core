<?php

namespace KieranFYI\Roles\Core\Traits;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Gate;

trait BuildsAccess
{
    use AuthorizesRequests;
    use ValidatesRequests;

    /**
     * @param User $user
     * @return void
     */
    protected function buildAccess(User $user): void
    {
        $access = [];
        foreach ($this->resourceAbilityMap() as $method => $policy) {
            if (in_array($method, $this->resourceMethodsWithoutModels())) {
                continue;
            }
            $access[$method] = Gate::any($policy, $user);
        }
        $user->setAttribute('access', $access);
    }
}