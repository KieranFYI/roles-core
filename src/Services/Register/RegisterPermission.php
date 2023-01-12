<?php

namespace KieranFYI\Roles\Core\Services\Register;

use Illuminate\Contracts\Support\Arrayable;
use KieranFYI\Roles\Core\Events\Register\RegisterPermissionEvent;
use KieranFYI\Roles\Core\Policies\AbstractPolicy;
use TypeError;

class RegisterPermission implements Arrayable
{
    /**
     * @var array
     */
    private static array $permissions = [];

    /**
     * @var array
     */
    private static array $policies = [];

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $description;

    /**
     * @var int
     */
    private int $power;

    /**
     * @var string|null
     */
    private ?string $group;

    public function __construct(string $name, string $description = '', int $power = 0, string $group = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->power = $power;
        $this->group = $group;
    }

    /**
     * @param string $name
     * @param string $description
     * @param int $power
     * @param string|null $group
     * @return RegisterPermission
     */
    public static function register(string $name, string $description = '', int $power = 0, string $group = null): RegisterPermission
    {
        if (!isset(self::$permissions[$name])) {
            self::$permissions[$name] = new static($name, $description, $power, $group);
        }
        return self::$permissions[$name];
    }

    /**
     * @param string $policy
     */
    public static function policy(string $policy): void
    {
        if (!is_a($policy, AbstractPolicy::class, true)) {
            throw new TypeError(self::class . '::registerPolicies(): policy must be of type ' . AbstractPolicy::class);
        }

        self::$policies[] = $policy;
    }

    /**
     * @return array
     */
    public static function policies(): array
    {
        return self::$policies;
    }

    /**
     * @return array
     */
    public static function permissions() {
        return self::$permissions;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'power' => $this->power,
            'group' => $this->group
        ];
    }
}