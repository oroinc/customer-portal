<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserSettings;
use Oro\Bundle\CustomerBundle\EventListener\PreferredLocalizationFormViewListener;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormView;
use Twig\Environment;

class PreferredLocalizationFormViewListenerTest extends TestCase
{
    private WebsiteManager&MockObject $websiteManager;
    private PreferredLocalizationFormViewListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->listener = new PreferredLocalizationFormViewListener($this->websiteManager);
    }

    public function testOnEntityEdit(): void
    {
        $template = '<div>Some template</div>';
        $formView = new FormView();
        $env = $this->createMock(Environment::class);
        $env->expects($this->once())
            ->method('render')
            ->with(
                '@OroCustomer/CustomerUser/widget/preferredLocalizationForm.html.twig',
                ['form' => $formView]
            )
            ->willReturn($template);
        $scrollData = new ScrollData(['dataBlocks' => [0 => ['subblocks' => [0 => []]]]]);
        $event = new BeforeListRenderEvent($env, $scrollData, new \stdClass(), $formView);

        $this->listener->onEntityEdit($event);
        $expectedScrollData = [
            'dataBlocks' => [
                0 => [
                    'subblocks' => [
                        0 => [
                            'data' => [
                                $template,
                            ],
                        ],
                    ],
                ],
            ],
        ];
        self::assertEquals($expectedScrollData, $scrollData->getData());
    }

    public function testOnEntityViewWhenNoCustomerUserSettings(): void
    {
        $this->websiteManager->expects($this->once())
            ->method('getDefaultWebsite')
            ->willReturn(new Website());
        $env = $this->createMock(Environment::class);
        $env->expects($this->never())
            ->method('render');
        $scrollData = new ScrollData(['dataBlocks' => [0 => ['subblocks' => [0 => []]]]]);
        $event = new BeforeListRenderEvent($env, $scrollData, new CustomerUser());

        $this->listener->onEntityView($event);
        self::assertEquals(['dataBlocks' => [0 => ['subblocks' => [0 => []]]]], $scrollData->getData());
    }

    public function testOnEntityViewWhenNoLocalizationInCustomerUserSettings(): void
    {
        $defaultWebsite = new Website();
        $customerUser = new CustomerUser();
        $customerUser->setWebsiteSettings(new CustomerUserSettings($defaultWebsite));
        $this->websiteManager->expects($this->once())
            ->method('getDefaultWebsite')
            ->willReturn($defaultWebsite);
        $env = $this->createMock(Environment::class);
        $env->expects($this->never())
            ->method('render');
        $scrollData = new ScrollData(['dataBlocks' => [0 => ['subblocks' => [0 => []]]]]);
        $event = new BeforeListRenderEvent($env, $scrollData, $customerUser);

        $this->listener->onEntityView($event);
        self::assertEquals(['dataBlocks' => [0 => ['subblocks' => [0 => []]]]], $scrollData->getData());
    }

    public function testOnEntityView(): void
    {
        $localization = new Localization();
        $defaultWebsite = new Website();
        $customerUser = new CustomerUser();
        $customerUser->setWebsiteSettings(
            (new CustomerUserSettings($defaultWebsite))->setLocalization($localization)
        );
        $this->websiteManager->expects($this->once())
            ->method('getDefaultWebsite')
            ->willReturn($defaultWebsite);
        $template = '<div>Some template</div>';
        $env = $this->createMock(Environment::class);
        $env->expects($this->once())
            ->method('render')
            ->with(
                '@OroCustomer/CustomerUser/widget/preferredLocalizationView.html.twig',
                ['preferredLocalization' => $localization]
            )
            ->willReturn($template);
        $scrollData = new ScrollData(['dataBlocks' => [0 => ['subblocks' => [0 => []]]]]);
        $event = new BeforeListRenderEvent($env, $scrollData, $customerUser);

        $this->listener->onEntityView($event);
        $expectedScrollData = [
            'dataBlocks' => [
                0 => [
                    'subblocks' => [
                        0 => [
                            'data' => [
                                $template,
                            ],
                        ],
                    ],
                ],
            ],
        ];
        self::assertEquals($expectedScrollData, $scrollData->getData());
    }
}
