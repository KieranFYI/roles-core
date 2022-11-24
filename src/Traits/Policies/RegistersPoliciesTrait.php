<?php

namespace KieranFYI\Roles\Traits\Policies;

use Illuminate\Support\Facades\Gate;
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
                throw new TypeError('Invalid Type on "policies", array expected');
            }

            return $this->policies;
        }
        return [];
    }
}