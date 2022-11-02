<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserSettings;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserPreferredLocalizationProvider;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManager;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class CustomerUserPreferredLocalizationProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var UserLocalizationManager|\PHPUnit\Framework\MockObject\MockObject */
    private $userLocalizationManager;

    /** @var CustomerUserPreferredLocalizationProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->userLocalizationManager = $this->createMock(UserLocalizationManager::class);
        $this->provider = new CustomerUserPreferredLocalizationProvider($this->userLocalizationManager);
    }

    /**
     * @dataProvider supportsDataProvider
     *
     * @param object $entity
     * @param bool $isSupported
     */
    public function testSupports($entity, bool $isSupported): void
    {
        $this->assertSame($isSupported, $this->provider->supports($entity));

        if (!$isSupported) {
            $this->expectException(\LogicException::class);
            $this->provider->getPreferredLocalization($entity);
        }
    }

    public function supportsDataProvider(): array
    {
        return [
            'supported' => [
                'entity' => (new CustomerUser())->setIsGuest(false),
                'isSupported' => true,
            ],
            'not supported guests' => [
                'entity' => (new CustomerUser())->setIsGuest(true),
                'isSupported' => false,
            ],
            'not supported' => [
                'entity' => new \stdClass(),
                'isSupported' => false,
            ],
        ];
    }

    public function testGetPreferredLocalizationForEntity(): void
    {
        $website = new Website();
        $customerUser = new CustomerUser();
        $customerUserSettings = new CustomerUserSettings($website);
        $customerUser->setWebsiteSettings($customerUserSettings);

        $localization = new Localization();
        $this->userLocalizationManager->expects($this->once())
            ->method('getCurrentLocalizationByCustomerUser')
            ->with($customerUser, $website)
            ->willReturn($localization);

        $this->assertEquals($localization, $this->provider->getPreferredLocalization($customerUser));
    }

    public function testGetPreferredLocalizationForEntityWithoutSettings(): void
    {
        $customerUser = new CustomerUser();

        $this->userLocalizationManager->expects($this->once())
            ->method('getCurrentLocalizationByCustomerUser')
            ->with($customerUser, null)
            ->willReturn(null);

        $this->assertNull($this->provider->getPreferredLocalization($customerUser));
    }
}
