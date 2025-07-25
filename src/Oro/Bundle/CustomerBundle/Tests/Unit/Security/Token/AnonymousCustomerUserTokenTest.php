<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Token;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class AnonymousCustomerUserTokenTest extends TestCase
{
    use EntityTrait;

    public function testGetters(): void
    {
        $visitor = new CustomerVisitor();
        ReflectionUtil::setId($visitor, 1);
        $organization = new Organization();
        ReflectionUtil::setId($organization, 3);

        $token = new AnonymousCustomerUserToken($visitor, [], $organization);

        self::assertSame($visitor, $token->getVisitor());
        self::assertSame($organization, $token->getOrganization());

        self::assertSame([], $token->getCredentials());
        $credentials = ['pass'];
        $token->setCredentials($credentials);
        self::assertEquals($credentials, $token->getCredentials());
    }

    public function testSerialization(): void
    {
        $role = new CustomerUserRole();
        ReflectionUtil::setId($role, 2);
        $visitor = new CustomerVisitor();
        ReflectionUtil::setId($visitor, 1);
        $organization = new Organization();
        ReflectionUtil::setId($organization, 3);

        $token = new AnonymousCustomerUserToken($visitor, [$role], $organization);

        /** @var AnonymousCustomerUserToken $newToken */
        $newToken = unserialize(serialize($token));
        self::assertEquals($token->getUser(), $newToken->getUser());
        self::assertNotSame($token->getRoles()[0], $newToken->getRoles()[0]);
        self::assertEquals($token->getRoles()[0]->getId(), $newToken->getRoles()[0]->getId());

        self::assertNotSame($token->getOrganization(), $newToken->getOrganization());
        self::assertEquals($token->getOrganization()->getId(), $newToken->getOrganization()->getId());
    }
}
