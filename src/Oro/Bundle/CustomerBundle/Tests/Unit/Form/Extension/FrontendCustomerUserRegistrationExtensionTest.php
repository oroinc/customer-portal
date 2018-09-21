<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserSettings;
use Oro\Bundle\CustomerBundle\Form\Extension\FrontendCustomerUserRegistrationExtension;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRegistrationType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension\Stub\FrontendCustomerUserRegistrationTypeStub;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Symfony\Component\Form\PreloadedExtension;

class FrontendCustomerUserRegistrationExtensionTest extends FormIntegrationTestCase
{
    private const DEFAULT_LOCALIZATION = 'en';
    private const CURRENT_LOCALIZATION = 'fr_FR';

    use EntityTrait;

    /**
     * @var WebsiteManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteManager;

    /**
     * @var LocalizationHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localizationHelper;

    /**
     * @var FrontendCustomerUserRegistrationExtension
     */
    private $extension;

    protected function setUp()
    {
        $this->localizationHelper = $this->createMock(LocalizationHelper::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->extension = new FrontendCustomerUserRegistrationExtension(
            $this->localizationHelper,
            $this->websiteManager
        );

        parent::setUp();
    }

    public function testGetExtendedType()
    {
        $this->assertEquals(FrontendCustomerUserRegistrationType::class, $this->extension->getExtendedType());
    }

    public function testOnPostSubmitWhenWebsiteSettingsExist(): void
    {
        /** @var Website $website */
        $website = $this->getEntity(Website::class);
        $defaultLocalization = $this->getEntity(
            Localization::class,
            ['id' => 2, 'formattingCode' => self::DEFAULT_LOCALIZATION]
        );

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntity(
            CustomerUser::class,
            [
                'websiteSettings' => (new CustomerUserSettings($website))->setLocalization($defaultLocalization)
            ]
        );

        $this->websiteManager
            ->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $currentLocalization = $this->getEntity(
            Localization::class,
            ['id' => 1, 'formattingCode' => self::CURRENT_LOCALIZATION]
        );

        $this->localizationHelper
            ->expects($this->once())
            ->method('getCurrentLocalization')
            ->willReturn($currentLocalization);

        $form = $this->factory->create(FrontendCustomerUserRegistrationType::NAME, $customerUser);

        $form->submit([]);

        $expectedWebsiteSettings = new CustomerUserSettings($website);
        $expectedWebsiteSettings->setLocalization($currentLocalization);
        $expectedWebsiteSettings->setCustomerUser($customerUser);

        self::assertEquals($expectedWebsiteSettings, $customerUser->getWebsiteSettings($website));
    }

    public function testOnPostSubmitWhenNoWebsiteSettingsExist(): void
    {
        $website = new Website();
        $customerUser = new CustomerUser();

        $this->websiteManager
            ->expects($this->any())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $currentLocalization = $this->getEntity(
            Localization::class,
            ['id' => 1, 'formattingCode' => self::CURRENT_LOCALIZATION]
        );

        $this->localizationHelper
            ->expects($this->once())
            ->method('getCurrentLocalization')
            ->willReturn($currentLocalization);

        $form = $this->factory->create(FrontendCustomerUserRegistrationType::NAME, $customerUser);

        $form->submit([]);

        $expectedWebsiteSettings = new CustomerUserSettings($website);
        $expectedWebsiteSettings->setLocalization($currentLocalization);
        $expectedWebsiteSettings->setCustomerUser($customerUser);

        self::assertEquals($expectedWebsiteSettings, $customerUser->getWebsiteSettings($website));
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        return [
            new PreloadedExtension(
                [
                    FrontendCustomerUserRegistrationType::NAME => new FrontendCustomerUserRegistrationTypeStub()
                ],
                [
                    FrontendCustomerUserRegistrationType::NAME => [$this->extension],
                ]
            ),
        ];
    }
}
