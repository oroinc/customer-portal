<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserAwareEntityPreferredLocalizationProvider;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Provider\PreferredLocalizationProviderInterface;
use Oro\Bundle\OrderBundle\Entity\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerUserAwareEntityPreferredLocalizationProviderTest extends TestCase
{
    private PreferredLocalizationProviderInterface|MockObject $customerUserPreferredLocalizationProvider;

    private CustomerUserAwareEntityPreferredLocalizationProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->customerUserPreferredLocalizationProvider = $this->createMock(
            PreferredLocalizationProviderInterface::class
        );

        $this->provider = new CustomerUserAwareEntityPreferredLocalizationProvider(
            $this->customerUserPreferredLocalizationProvider
        );
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

    public function testGetPreferredLocalization(): void
    {
        $customerUser = new CustomerUser();
        $entity = (new Order())
            ->setCustomerUser($customerUser);
        $localization = new Localization();

        $this->customerUserPreferredLocalizationProvider
            ->expects(self::once())
            ->method('getPreferredLocalization')
            ->with($customerUser)
            ->willReturn($localization);

        self::assertSame($localization, $this->provider->getPreferredLocalization($entity));
    }
}
