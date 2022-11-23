<?php

namespace KieranFYI\Roles\Traits\Policies;

use Illuminate\Support\Facades\Gate;

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
            return $this->policies;
        }
        return [];
    }
}