<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Group;

use Oro\Bundle\CustomerBundle\Acl\Group\AclGroupProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

class AclGroupProviderTest extends \PHPUnit\Framework\TestCase
{
    const LOCAL_LEVEL = 'Oro\Bundle\CustomerBundle\Entity\Customer';
    const BASIC_LEVEL = 'Oro\Bundle\CustomerBundle\Entity\CustomerUser';

    /** @var \PHPUnit\Framework\MockObject\MockObject|TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var AclGroupProvider */
    protected $provider;

    protected function setUp()
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->provider = new AclGroupProvider($this->tokenAccessor);
    }

    protected function tearDown()
    {
        unset($this->tokenAccessor, $this->provider);
    }

    /**
     * @dataProvider supportsDataProvider
     *
     * @param object|null $user
     * @param bool $expectedResult
     */
    public function testSupports($user, $expectedResult)
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals($expectedResult, $this->provider->supports());
    }

    /**
     * @return array
     */
    public function supportsDataProvider()
    {
        return [
            'incorrect user object' => [
                'user' => new \stdClass(),
                'expectedResult' => false
            ],
            'customer user' => [
                'user' => new CustomerUser(),
                'expectedResult' => true
            ],
            'user is not logged in' => [
                'user' => null,
                'expectedResult' => true
            ],
        ];
    }

    public function testGetGroup()
    {
        $this->assertEquals(CustomerUser::SECURITY_GROUP, $this->provider->getGroup());
    }
}
