<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;
use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserAuthenticationProvider;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AnonymousCustomerUserAuthenticationProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    private const ENTITY_ID = 3;
    private const SESSION_ID = 5;
    private const UPDATE_LATENCY = 500;

    /** @var CustomerVisitorManager|\PHPUnit\Framework\MockObject\MockObject */
    private $visitorManager;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var AnonymousCustomerUserAuthenticationProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->visitorManager = $this->createMock(CustomerVisitorManager::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->provider = new AnonymousCustomerUserAuthenticationProvider(
            $this->visitorManager,
            $this->websiteManager
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
