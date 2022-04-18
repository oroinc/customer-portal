<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserLoggingInfoProvider;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ServerBag;

class CustomerUserLoggingInfoProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestStack|\PHPUnit\Framework\MockObject\MockObject */
    private $requestStack;

    /** @var CustomerUserLoggingInfoProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);

        $this->provider = new CustomerUserLoggingInfoProvider($this->requestStack);
    }

    public function testGetUserLoggingInfo(): void
    {
        $request = $this->createMock(Request::class);
        $this->requestStack->expects($this->any())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $request->expects($this->once())
            ->method('getClientIp')
            ->willReturn('127.0.0.1');

        $request->server = new ServerBag([
            'HTTP_SEC_CH_UA_PLATFORM' => '"macOS"',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\/537.36'
        ]);

        $this->assertEquals(
            [
                'customer_user' => [
                    'id' => 123,
                    'email' => 'john@example.com',
                    'fullname' => 'John Doe',
                    'enabled' => true,
                    'confirmed' => true,
                    'lastlogin' => new \DateTime('01-01-2010'),
                    'createdat' => new \DateTime('01-01-2000'),
                ],
                'ipaddress' => '127.0.0.1',
                'platform' => 'macOS',
                'user agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\/537.36'
            ],
            $this->provider->getUserLoggingInfo($this->getCustomerUserWithData())
        );
    }

    public function testGetUserLoggingInfoWithoutRequest(): void
    {
        $this->requestStack->expects($this->exactly(2))
            ->method('getCurrentRequest')
            ->willReturn(null);

        $result = $this->provider->getUserLoggingInfo($this->getCustomerUserWithData());
        self::assertArrayNotHasKey('ipaddress', $result);
        self::assertArrayNotHasKey('customer_user_email', $result);
        self::assertArrayHasKey('customer_user', $result);
    }

    public function testSetUserInfoForString()
    {
        self::assertSame(
            ['customer_user_email' => 'some username'],
            $this->provider->getUserLoggingInfo('some username')
        );
    }

    private function getCustomerUserWithData(): CustomerUser
    {
        $user = new CustomerUser();
        ReflectionUtil::setId($user, 123);
        $user->setEnabled(true);
        $user->setConfirmed(true);
        $user->setEmail('john@example.com');
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setLastLogin(new \DateTime('01-01-2010'));
        $user->setCreatedAt(new \DateTime('01-01-2000'));

        return $user;
    }
}
