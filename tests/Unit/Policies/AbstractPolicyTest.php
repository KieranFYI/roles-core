<?php

namespace KieranFYI\Tests\Roles\Core\Unit\Policies;

use Illuminate\Foundation\Auth\User;
use KieranFYI\Tests\Roles\Core\Policies\Policy;
use KieranFYI\Tests\Roles\Core\TestCase;

class AbstractPolicyTest extends TestCase
{

    /**
     * @var Policy
     */
    private Policy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new Policy();
    }

    public function testViewAnyNullUser()
    {
        $this->assertFalse($this->policy->viewAny(null));
    }

    public function testViewNullUser()
    {
        $this->assertFalse($this->policy->view(null, new User()));
    }

    public function testCreateNullUser()
    {
        $this->assertFalse($this->policy->create(null));
    }

    public function testUpdateNullUser()
    {
        $this->assertFalse($this->policy->update(null, new User()));
    }

    public function testDeleteNullUser()
    {
        $this->assertFalse($this->policy->delete(null, new User()));
    }

    public function testRestoreNullUser()
    {
        $this->assertFalse($this->policy->restore(null, new User()));
    }

    public function testForceDeleteNullUser()
    {
        $this->assertFalse($this->policy->forceDelete(null, new User()));
    }
}