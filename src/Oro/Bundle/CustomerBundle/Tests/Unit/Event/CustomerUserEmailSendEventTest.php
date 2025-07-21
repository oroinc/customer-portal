<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Event;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Event\CustomerUserEmailSendEvent;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use PHPUnit\Framework\TestCase;

class CustomerUserEmailSendEventTest extends TestCase
{
    private CustomerUserEmailSendEvent $event;
    private CustomerUser $customerUser;
    private Website $website;

    #[\Override]
    protected function setUp(): void
    {
        $this->customerUser = new CustomerUser();
        $this->website = new Website();
        $this->event = new CustomerUserEmailSendEvent($this->customerUser, 'template_name', []);
        $this->event->setScope($this->website);
    }

    /**
     * Test setters getters
     */
    public function testAccessors(): void
    {
        $this->event->setEmailTemplate('new_template');
        $this->event->setEmailTemplateParams(['foo' => 'bar']);
        $this->assertSame('new_template', $this->event->getEmailTemplate());
        $this->assertSame(['foo' => 'bar'], $this->event->getEmailTemplateParams());
        $this->assertSame($this->customerUser, $this->event->getCustomerUser());
        $this->assertSame($this->website, $this->event->getScope());
    }
}
