<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;
use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserAuthenticationProvider;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Component\Testing\Unit\EntityTrait;

class AnonymousCustomerUserAuthenticationProviderTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    const ENTITY_ID = 3;
    const SESSION_ID = 5;
    const UPDATE_LATENCY = 500;

    /**
     * @var CustomerVisitorManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $visitorManager;

    /**
     * @var WebsiteManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteManager;

    /**
     * @var AnonymousCustomerUserAuthenticationProvider
     */
    protected $provider;


    protected function setUp()
    {
        $this->visitorManager = $this->getMockBuilder(CustomerVisitorManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->provider = new AnonymousCustomerUserAuthenticationProvider(
            $this->visitorManager,
            $this->websiteManager,
            self::UPDATE_LATENCY
        );
    }

    public function testSupportsValid()
    {
        $this->assertTrue(
            $this->provider->supports(new AnonymousCustomerUserToken('User'))
        );
    }

    public function testSupportsInvalid()
    {
        $this->assertFalse(
            $this->provider->supports($this->createMock(TokenInterface::class))
        );
    }

    public function testAuthenticateNotSupported()
    {
        $this->visitorManager->expects($this->never())
            ->method('findOrCreate');
        $this->assertNull($this->provider->authenticate($this->createMock(TokenInterface::class)));
    }

    public function testAuthenticate()
    {
        $token = new AnonymousCustomerUserToken('User', ['ROLE_FOO', 'ROLE_BAR']);
        $token->setCredentials(['visitor_id'=> self::ENTITY_ID, 'session_id'=> self::SESSION_ID]);
        $visitor = $this->getEntity(
            CustomerVisitor::class,
            ['id' => self::ENTITY_ID, 'session_id' => self::SESSION_ID]
        );

        $this->visitorManager->expects($this->once())
            ->method('findOrCreate')
            ->with(self::ENTITY_ID, self::SESSION_ID)
            ->willReturn($visitor);

        $this->visitorManager->expects($this->once())
            ->method('updateLastVisitTime')
            ->with($visitor, self::UPDATE_LATENCY);

        $organization = new Organization();
        $website = new Website();
        $website->setOrganization($organization);

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->assertEquals(
            new AnonymousCustomerUserToken(
                'User',
                ['ROLE_FOO', 'ROLE_BAR'],
                $visitor,
                $organization
            ),
            $this->provider->authenticate($token)
        );
    }
}
