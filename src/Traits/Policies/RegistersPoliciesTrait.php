<?php

namespace KieranFYI\Roles\Core\Traits\Policies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use KieranFYI\Roles\Core\Policies\AbstractPolicy;
use KieranFYI\Roles\Core\Services\Register\RegisterPermission;
use TypeError;

/**
 * @property array<class-string, class-string> $policies
 */
trait RegistersPoliciesTrait
{
    /**
     * Register the application's policies.
     *
     * @return void
     */
    public function registerPolicies()
    {
        foreach ($this->policies() as $model => $policy) {
            if (!is_a($model, Model::class, true)) {
                throw new TypeError(self::class . '::registerPolicies(): "' . $model . '" must be of type ' . Model::class);
            }

            if (!is_a($policy, AbstractPolicy::class, true)) {
                throw new TypeError(self::class . '::registerPolicies(): ' . $model . ' policy must be of type ' . AbstractPolicy::class);
            }

            RegisterPermission::policy($policy);
            Gate::policy($model, $policy);
        }
    }

    /**
     * Get the policies defined on the provider.
     *
     * @return array<class-string, class-string>
     */
    public function policies(): array
    {
        if (property_exists($this, 'policies')) {
            if (!is_array($this->policies)) {
                throw new TypeError(self::class.'::policies(): Property ($policies) must be of type array');
            }

            return $this->policies;
        }
        return [];
    }
}