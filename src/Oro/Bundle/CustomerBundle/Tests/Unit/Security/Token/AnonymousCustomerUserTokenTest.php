<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Token;

use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\CustomerBundle\Tests\Unit\Stub\CustomerUserRoleStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Stub\CustomerVisitorStub;
use Oro\Bundle\UserBundle\Tests\Unit\Stub\OrganizationStub;
use Oro\Component\Testing\Unit\EntityTrait;

class AnonymousCustomerUserTokenTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    public function testGetters()
    {
        $visitor = new CustomerVisitorStub(1);
        $organization = new OrganizationStub(3);

        $token = new AnonymousCustomerUserToken('user', [], $visitor, $organization);

        self::assertSame($visitor, $token->getVisitor());
        self::assertSame($organization, $token->getOrganization());

        self::assertSame([], $token->getCredentials());
        $credentials = ['pass'];
        $token->setCredentials($credentials);
        self::assertEquals($credentials, $token->getCredentials());
    }

    public function testSerialization()
    {
        $user = 'user';
        $role = (new CustomerUserRoleStub())->setId(2);
        $visitor = new CustomerVisitorStub(1);
        $organization = new OrganizationStub(3);

        $token = new AnonymousCustomerUserToken($user, [$role], $visitor, $organization);

        /** @var AnonymousCustomerUserToken $newToken */
        $newToken = unserialize(serialize($token));

        self::assertEquals($token->getUser(), $newToken->getUser());

        self::assertNull($newToken->getVisitor());

        self::assertNotSame($token->getRoles()[0], $newToken->getRoles()[0]);
        self::assertEquals($token->getRoles()[0]->getId(), $newToken->getRoles()[0]->getId());

        self::assertNotSame($token->getOrganization(), $newToken->getOrganization());
        self::assertEquals($token->getOrganization()->getId(), $newToken->getOrganization()->getId());
    }
}
