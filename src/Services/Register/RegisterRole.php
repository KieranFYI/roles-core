<?php

namespace KieranFYI\Roles\Core\Services\Register;

use Illuminate\Contracts\Support\Arrayable;
use KieranFYI\Roles\Core\Policies\AbstractPolicy;

class RegisterRole implements Arrayable
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var int
     */
    private int $display_order = 1;

    /**
     * @var string
     */
    private string $colour = '#8c8c8c';

    /**
     * @var bool
     */
    private bool $default = false;

    /**
     * @var array
     */
    private array $permissions = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     * @return RegisterRole
     */
    public static function register(string $name): RegisterRole
    {
        return new static($name);
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param int $display_order
     * @return $this
     */
    public function displayOrder(int $display_order): static
    {
        $this->display_order = $display_order;
        return $this;
    }

    /**
     * @param string $colour
     * @return $this
     */
    public function colour(string $colour): static
    {
        $this->colour = $colour;
        return $this;
    }

    /**
     * @return $this
     */
    public function default(): static
    {
        $this->default = true;
        return $this;
    }

    public function permission(string|AbstractPolicy $permission, array $methods = []): static
    {
        if (!is_subclass_of($permission, AbstractPolicy::class)) {
            $this->permissions[] = $permission;
            return $this;
        }

        /** @var AbstractPolicy $policy */
        $policy = new $permission;
        $this->permissions = array_merge($this->permissions, $policy->permissions($methods));
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'display_order' => $this->display_order,
            'colour' => $this->colour,
            'default' => $this->default,
            'permissions' => $this->permissions,
        ];
    }
}