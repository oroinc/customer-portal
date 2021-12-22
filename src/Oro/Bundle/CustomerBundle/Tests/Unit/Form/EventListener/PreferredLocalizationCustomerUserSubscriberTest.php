<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserSettings;
use Oro\Bundle\CustomerBundle\Form\EventListener\PreferredLocalizationCustomerUserSubscriber;
use Oro\Bundle\CustomerBundle\Form\Extension\PreferredLocalizationCustomerUserExtension;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class PreferredLocalizationCustomerUserSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var PreferredLocalizationCustomerUserSubscriber */
    private $subscriber;

    protected function setUp(): void
    {
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->registry = $this->createMock(ManagerRegistry::class);

        $this->subscriber = new PreferredLocalizationCustomerUserSubscriber(
            $this->websiteManager,
            $this->configManager,
            $this->registry
        );
    }

    public function testPostSetDataWhenNoPreferredLocalizationField(): void
    {
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('has')
            ->with(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD)
            ->willReturn(false);

        $form->expects($this->never())
            ->method('add');
        $form->expects($this->never())
            ->method('remove');
        $form->expects($this->never())
            ->method('get');

        $event = new FormEvent($form, (new CustomerUser())->setWebsite(new Website()));
        $this->subscriber->onPostSetData($event);
    }

    public function testOnPostSetDataWhenIsNotAvailable(): void
    {
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('has')
            ->with(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD)
            ->willReturn(true);
        $form->expects($this->once())
            ->method('remove')
            ->with(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD);
        $form->expects($this->never())
            ->method('get');
        $this->configManager->expects($this->once())
            ->method('get')
            ->with(Configuration::getConfigKeyByName(Configuration::ENABLED_LOCALIZATIONS))
            ->willReturn([]);

        $event = new FormEvent($form, (new CustomerUser())->setWebsite(new Website()));
        $this->subscriber->onPostSetData($event);
    }

    public function testOnPostSetDataWhenAvailableWithoutData(): void
    {
        $website = new Website();
        $this->configManager->expects($this->once())
            ->method('get')
            ->with(Configuration::getConfigKeyByName(Configuration::ENABLED_LOCALIZATIONS))
            ->willReturn([1, 2]);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('has')
            ->with(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD)
            ->willReturn(true);
        $form->expects($this->never())
            ->method('remove');
        $childForm = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('get')
            ->with(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD)
            ->willReturn($childForm);
        $childForm->expects($this->once())
            ->method('setData')
            ->with(null);

        $event = new FormEvent($form, (new CustomerUser())->setWebsite($website));
        $this->subscriber->onPostSetData($event);
    }

    public function testOnPostSetData(): void
    {
        $website = new Website();
        $this->configManager->expects($this->once())
            ->method('get')
            ->with(Configuration::getConfigKeyByName(Configuration::ENABLED_LOCALIZATIONS))
            ->willReturn([1, 2]);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('has')
            ->with(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD)
            ->willReturn(true);
        $form->expects($this->never())
            ->method('remove');
        $childForm = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('get')
            ->with(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD)
            ->willReturn($childForm);
        $localization = new Localization();
        $childForm->expects($this->once())
            ->method('setData')
            ->with($localization);

        $customerUser = (new CustomerUser())
            ->setWebsite($website)
            ->setWebsiteSettings(
                (new CustomerUserSettings($website))->setLocalization($localization)
            );
        $event = new FormEvent($form, $customerUser);
        $this->subscriber->onPostSetData($event);
    }

    public function testPostSubmitWhenNoPreferredLocalizationForm(): void
    {
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('has')
            ->with(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD)
            ->willReturn(false);
        $customerUser = new CustomerUser();

        $expectedCustomerUser = clone $customerUser;
        $event = new FormEvent($form, $customerUser);
        $this->subscriber->onPostSubmit($event);

        self::assertEquals($expectedCustomerUser, $customerUser);
    }

    public function testPostSubmitWhenNoSettings(): void
    {
        $preferredLocalization = new Localization();
        $preferredLocalizationForm = $this->createMock(FormInterface::class);
        $preferredLocalizationForm->expects($this->once())
            ->method('getData')
            ->willReturn($preferredLocalization);
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('has')
            ->with(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD)
            ->willReturn(true);
        $form->expects($this->once())
            ->method('get')
            ->with(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD)
            ->willReturn($preferredLocalizationForm);

        $website = new Website();
        $customerUser = new CustomerUser();
        $customerUser->setWebsite($website);
        $expectedCustomerUser = clone $customerUser;
        $expectedCustomerUser->setWebsiteSettings(
            (new CustomerUserSettings($website))->setLocalization($preferredLocalization)
        );

        $event = new FormEvent($form, $customerUser);
        $this->subscriber->onPostSubmit($event);

        self::assertEquals($expectedCustomerUser, $customerUser);
    }

    public function testPostSubmit(): void
    {
        $preferredLocalization = new Localization();
        $preferredLocalizationForm = $this->createMock(FormInterface::class);
        $preferredLocalizationForm->expects($this->once())
            ->method('getData')
            ->willReturn($preferredLocalization);
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('has')
            ->with(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD)
            ->willReturn(true);
        $form->expects($this->once())
            ->method('get')
            ->with(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD)
            ->willReturn($preferredLocalizationForm);

        $website = new Website();
        $customerUser = new CustomerUser();
        $customerUser->setWebsite($website);
        $expectedCustomerUser = clone $customerUser;
        $customerUser->setWebsiteSettings(
            (new CustomerUserSettings($website))->setLocalization(new Localization())
        );
        $expectedCustomerUser->setWebsiteSettings(
            (new CustomerUserSettings($website))->setLocalization($preferredLocalization)
        );

        $event = new FormEvent($form, $customerUser);
        $this->subscriber->onPostSubmit($event);

        self::assertEquals($expectedCustomerUser, $customerUser);
    }
}
