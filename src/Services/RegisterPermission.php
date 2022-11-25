<?php

namespace KieranFYI\Roles\Services;

use Illuminate\Contracts\Support\Arrayable;

class RegisterPermission implements Arrayable
{
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