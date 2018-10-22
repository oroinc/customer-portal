<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserPreferredLanguageProvider;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManager;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\TranslationBundle\Entity\Language;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class CustomerUserPreferredLanguageProviderTest extends \PHPUnit_Framework_TestCase
{
    private const LANGUAGE = 'fr_FR';

    /**
     * @var UserLocalizationManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userLocalizationManager;

    /**
     * @var CustomerUserPreferredLanguageProvider
     */
    private $provider;

    protected function setUp()
    {
        $this->userLocalizationManager = $this->createMock(UserLocalizationManager::class);

        $this->provider = new CustomerUserPreferredLanguageProvider($this->userLocalizationManager);
    }

    public function testSupports(): void
    {
        self::assertTrue($this->provider->supports(new CustomerUser()));
    }

    public function testSupportsFail(): void
    {
        self::assertFalse($this->provider->supports(new \stdClass()));
        self::assertFalse($this->provider->supports(new User()));
        self::assertFalse($this->provider->supports((new CustomerUser())->setIsGuest(true)));
    }

    public function testGetPreferredLanguageForNotSupportedEntity(): void
    {
        $this->expectException(\LogicException::class);

        $this->provider->getPreferredLanguage(new User());
    }

    public function testGetPreferredLanguageWhenCurrentLocalizationExists(): void
    {
        $localization = (new Localization())->setLanguage((new Language())->setCode(self::LANGUAGE));
        $customerUser = new CustomerUser();
        $this->userLocalizationManager
            ->expects($this->once())
            ->method('getCurrentLocalizationByCustomerUser')
            ->with($customerUser)
            ->willReturn($localization);

        self::assertEquals(self::LANGUAGE, $this->provider->getPreferredLanguage($customerUser));
    }

    public function testGetPreferredLanguageWhenPrimaryWebsiteLocalizationExists(): void
    {
        $website = new Website();
        $customerUser = (new CustomerUser())->setWebsite($website);

        $localization = (new Localization())->setLanguage((new Language())->setCode(self::LANGUAGE));
        $this->userLocalizationManager
            ->expects($this->exactly(2))
            ->method('getCurrentLocalizationByCustomerUser')
            ->withConsecutive([$customerUser], [$customerUser, $website])
            ->willReturnOnConsecutiveCalls(null, $localization);


        self::assertEquals(self::LANGUAGE, $this->provider->getPreferredLanguage($customerUser));
    }

    public function testGetPreferredLanguageWhenNoPrimaryWebsiteExist(): void
    {
        $customerUser = new CustomerUser();

        $this->userLocalizationManager
            ->expects($this->once())
            ->method('getCurrentLocalizationByCustomerUser')
            ->with($customerUser)
            ->willReturn(null);

        self::assertEquals(Configuration::DEFAULT_LANGUAGE, $this->provider->getPreferredLanguage($customerUser));
    }

    public function testGetPreferredLanguageWhenPrimaryWebsiteLocalizationNotExists(): void
    {
        $website = new Website();
        $customerUser = (new CustomerUser())->setWebsite($website);

        $this->userLocalizationManager
            ->expects($this->exactly(2))
            ->method('getCurrentLocalizationByCustomerUser')
            ->withConsecutive([$customerUser], [$customerUser, $website])
            ->willReturnOnConsecutiveCalls(null, null);

        self::assertEquals(Configuration::DEFAULT_LANGUAGE, $this->provider->getPreferredLanguage($customerUser));
    }
}
