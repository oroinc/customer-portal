<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Token;

use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\SecurityBundle\Tests\Unit\Authentication\Token\OrganizationContextTrait;
use Oro\Bundle\UserBundle\Entity\User;

class AnonymousCustomerUserTokenTest extends \PHPUnit_Framework_TestCase
{
    use OrganizationContextTrait;

    public function testOrganizationContextSerialization(): void
    {
        $user = $this->getEntity(User::class, ['id' => 7]);
        $token = new AnonymousCustomerUserToken($user);

        $this->assertOrganizationContextSerialization($token);
    }
}
