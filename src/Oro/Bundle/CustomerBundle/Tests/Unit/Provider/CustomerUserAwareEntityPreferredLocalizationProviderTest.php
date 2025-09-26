<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserAwareEntityPreferredLocalizationProvider;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManagerInterface;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Provider\PreferredLocalizationProviderInterface;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class CustomerUserAwareEntityPreferredLocalizationProviderTest extends TestCase
{
    private PreferredLocalizationProviderInterface&MockObject $customerUserPreferredLocalizationProvider;
    private UserLocalizationManagerInterface&MockObject $userLocalizationManager;
    private CustomerUserAwareEntityPreferredLocalizationProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->customerUserPreferredLocalizationProvider = $this->createMock(
            PreferredLocalizationProviderInterface::class
        );
        $this->userLocalizationManager = $this->createMock(UserLocalizationManagerInterface::class);

        $this->provider = new CustomerUserAwareEntityPreferredLocalizationProvider(
            $this->customerUserPreferredLocalizationProvider
        );
        $this->provider->setUserLocalizationManager($this->userLocalizationManager);
    }

    public function testSupportsWhenNotCustomerOwnerAwareInterface(): void
    {
        self::assertFalse($this->provider->supports(new \stdClass()));
    }

    public function testSupportsWhenNoCustomerUser(): void
    {
        $entity = new Order();

        self::assertFalse($this->provider->supports($entity));
    }

    public function testSupportsWhenHasGuestCustomerUser(): void
    {
        $customerUser = (new CustomerUser())
            ->setIsGuest(true);
        $entity = (new Order())
            ->setCustomerUser($customerUser);

        self::assertFalse($this->provider->supports($entity));
    }

    public function testSupportsWhenRegularCustomerUser(): void
    {
        $customerUser = new CustomerUser();
        $entity = (new Order())
            ->setCustomerUser($customerUser);

        self::assertTrue($this->provider->supports($entity));
    }

    public function testGetPreferredLocalizationFromCurrentWebsite(): void
    {
        $customerUser = new CustomerUser();
        $entity = (new Order())
            ->setCustomerUser($customerUser);
        $localization = new Localization();

        $this->userLocalizationManager->expects(self::once())
            ->method('getCurrentLocalizationByCustomerUser')
            ->with($customerUser)
            ->willReturn($localization);

        self::assertSame($localization, $this->provider->getPreferredLocalization($entity));
    }

    public function testGetPreferredLocalizationFromPrimaryWebsiteWhenCurrentIsNull(): void
    {
        $website = new Website();
        $customerUser = (new CustomerUser())
            ->setWebsite($website);
        $entity = (new Order())
            ->setCustomerUser($customerUser);
        $localization = new Localization();

        $this->userLocalizationManager->expects(self::exactly(2))
            ->method('getCurrentLocalizationByCustomerUser')
            ->willReturnMap([
                [$customerUser, null, null],
                [$customerUser, $website, $localization]
            ]);

        self::assertSame($localization, $this->provider->getPreferredLocalization($entity));
    }

    public function testGetPreferredLocalizationReturnsNullWhenNoWebsite(): void
    {
        $customerUser = new CustomerUser();
        $entity = (new Order())
            ->setCustomerUser($customerUser);

        $this->userLocalizationManager->expects(self::once())
            ->method('getCurrentLocalizationByCustomerUser')
            ->with($customerUser)
            ->willReturn(null);

        self::assertNull($this->provider->getPreferredLocalization($entity));
    }

    public function testGetPreferredLocalizationReturnsNullWhenBothWebsitesReturnNull(): void
    {
        $website = new Website();
        $customerUser = (new CustomerUser())
            ->setWebsite($website);
        $entity = (new Order())
            ->setCustomerUser($customerUser);

        $this->userLocalizationManager->expects(self::exactly(2))
            ->method('getCurrentLocalizationByCustomerUser')
            ->willReturnMap([
                [$customerUser, null, null],
                [$customerUser, $website, null]
            ]);

        self::assertNull($this->provider->getPreferredLocalization($entity));
    }

    public function testGetPreferredLocalizationWithBcLayerWhenUserLocalizationManagerIsNull(): void
    {
        $customerUser = new CustomerUser();
        $entity = (new Order())
            ->setCustomerUser($customerUser);
        $localization = new Localization();

        // Create provider without setting userLocalizationManager
        $provider = new CustomerUserAwareEntityPreferredLocalizationProvider(
            $this->customerUserPreferredLocalizationProvider
        );

        $this->customerUserPreferredLocalizationProvider->expects(self::once())
            ->method('getPreferredLocalization')
            ->with($customerUser)
            ->willReturn($localization);

        self::assertSame($localization, $provider->getPreferredLocalization($entity));
    }

    public function testGetPreferredLocalizationWithBcLayerReturnsNull(): void
    {
        $customerUser = new CustomerUser();
        $entity = (new Order())
            ->setCustomerUser($customerUser);

        // Create provider without setting userLocalizationManager
        $provider = new CustomerUserAwareEntityPreferredLocalizationProvider(
            $this->customerUserPreferredLocalizationProvider
        );

        $this->customerUserPreferredLocalizationProvider->expects(self::once())
            ->method('getPreferredLocalization')
            ->with($customerUser)
            ->willReturn(null);

        self::assertNull($provider->getPreferredLocalization($entity));
    }

    public function testSupportsWithBcLayerWhenUserLocalizationManagerIsNull(): void
    {
        $customerUser = new CustomerUser();
        $entity = (new Order())
            ->setCustomerUser($customerUser);

        // Create provider without setting userLocalizationManager
        $provider = new CustomerUserAwareEntityPreferredLocalizationProvider(
            $this->customerUserPreferredLocalizationProvider
        );

        self::assertTrue($provider->supports($entity));
    }
}
