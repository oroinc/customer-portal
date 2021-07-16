<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
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

    public function testGetPreferredLocalizationByCurrentWebsite(): void
    {
        $entity = (new CustomerUser())->setIsGuest(false);

        $localization = new Localization();
        $this->userLocalizationManager->expects($this->once())
            ->method('getCurrentLocalizationByCustomerUser')
            ->with($this->identicalTo($entity))
            ->willReturn($localization);

        $this->assertSame($localization, $this->provider->getPreferredLocalization($entity));
    }

    public function testGetPreferredLocalizationWithoutWebsite(): void
    {
        $entity = (new CustomerUser())->setIsGuest(false);

        $this->userLocalizationManager->expects($this->once())
            ->method('getCurrentLocalizationByCustomerUser')
            ->with($this->identicalTo($entity))
            ->willReturn(null);

        $this->assertNull($this->provider->getPreferredLocalization($entity));
    }

    public function testGetPreferredLocalizationByPrimaryWebsite(): void
    {
        $website = new Website();
        $entity = (new CustomerUser())
            ->setIsGuest(false)
            ->setWebsite($website);

        $localization = new Localization();
        $this->userLocalizationManager->expects($this->exactly(2))
            ->method('getCurrentLocalizationByCustomerUser')
            ->withConsecutive(
                [$this->identicalTo($entity)],
                [$this->identicalTo($entity), $this->identicalTo($website)]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                $localization
            );

        $this->assertSame($localization, $this->provider->getPreferredLocalization($entity));
    }
}
