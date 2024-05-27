<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Event\CustomerUserEmailSendEvent;
use Oro\Bundle\CustomerBundle\EventListener\AnonymousUserEmailSendListener;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AnonymousUserEmailSendListenerTest extends TestCase
{
    private TokenStorageInterface|MockObject $tokenStorage;
    private WebsiteManager|MockObject $websiteManager;
    private AnonymousUserEmailSendListener $listener;

    protected function setUp(): void
    {
        $this->websiteManager = self::createMock(WebsiteManager::class);
        $this->tokenStorage = self::createMock(TokenStorageInterface::class);

        $this->listener = new AnonymousUserEmailSendListener(
            $this->tokenStorage,
            $this->websiteManager
        );
    }

    public function testOnCustomerUserEmailSend(): void
    {
        $customerUser = new CustomerUser();
        $visitor = new CustomerVisitor();
        $visitor->setCustomerUser($customerUser);
        $token = new AnonymousCustomerUserToken('username', [], $visitor);

        $website = new Website();
        $this->websiteManager
            ->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->tokenStorage
            ->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $event = new CustomerUserEmailSendEvent(new CustomerUser(), 'template', []);

        $this->listener->onCustomerUserEmailSend($event);
        self::assertSame($website, $event->getScope());
    }
}
