<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Form\EventSubscriber;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Entity\WebsiteAwareInterface;
use Oro\Bundle\WebsiteBundle\Form\EventSubscriber\DefaultWebsiteSubscriber;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class DefaultWebsiteSubscriberTest extends TestCase
{
    /**
     * @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $websiteManagerMock;

    /**
     * @var DefaultWebsiteSubscriber
     */
    private $subscriber;

    public function setUp()
    {
        $this->websiteManagerMock = $this->createMock(WebsiteManager::class);

        $this->subscriber = new DefaultWebsiteSubscriber($this->websiteManagerMock);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            [
                FormEvents::SUBMIT => ['onSubmit', 10],
            ],
            DefaultWebsiteSubscriber::getSubscribedEvents()
        );
    }

    public function testOnSubmit()
    {
        $formMock = $this->createFormMock();
        $websiteMock = $this->createWebsiteMock();
        $websiteAwareMock = $this->createWebsiteAwareMock();

        $this->websiteManagerMock
            ->expects(self::once())
            ->method('getDefaultWebsite')
            ->willReturn($websiteMock);

        $websiteAwareMock
            ->expects(self::once())
            ->method('getWebsite')
            ->willReturn(null);

        $websiteAwareMock
            ->expects(self::once())
            ->method('setWebsite')
            ->with($websiteMock);

        $this->subscriber->onSubmit(new FormEvent($formMock, $websiteAwareMock));
    }

    public function testWrongData()
    {
        $formMock = $this->createFormMock();

        $this->websiteManagerMock
            ->expects(self::never())
            ->method('getDefaultWebsite');

        $this->subscriber->onSubmit(new FormEvent($formMock, null));
    }

    public function testWebsiteAlreadySet()
    {
        $formMock = $this->createFormMock();
        $websiteMock = $this->createWebsiteMock();
        $websiteAwareMock = $this->createWebsiteAwareMock();

        $this->websiteManagerMock
            ->expects(self::never())
            ->method('getDefaultWebsite');

        $websiteAwareMock
            ->expects(self::once())
            ->method('getWebsite')
            ->willReturn($websiteMock);

        $websiteAwareMock
            ->expects(self::never())
            ->method('setWebsite')
            ->with($websiteMock);

        $this->subscriber->onSubmit(new FormEvent($formMock, $websiteAwareMock));
    }

    /**
     * @return FormInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createFormMock()
    {
        return $this->createMock(FormInterface::class);
    }

    /**
     * @return Website|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createWebsiteMock()
    {
        return $this->createMock(Website::class);
    }

    /**
     * @return WebsiteAwareInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createWebsiteAwareMock()
    {
        return $this->createMock(WebsiteAwareInterface::class);
    }
}
