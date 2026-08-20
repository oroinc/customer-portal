<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Async\PasswordResetRequestContext;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Provider\PasswordResetRequestPreferredLocalizationProvider;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\TestCase;

class PasswordResetRequestPreferredLocalizationProviderTest extends TestCase
{
    private PasswordResetRequestContext $passwordResetRequestContext;
    private PasswordResetRequestPreferredLocalizationProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->passwordResetRequestContext = new PasswordResetRequestContext();
        $this->provider = new PasswordResetRequestPreferredLocalizationProvider($this->passwordResetRequestContext);
    }

    public function testSupportsWhenNoLocalizationInContext(): void
    {
        self::assertFalse($this->provider->supports(new CustomerUser()));
    }

    public function testSupportsWhenNotCustomerUser(): void
    {
        $this->passwordResetRequestContext->setLocalization(new Localization());

        self::assertFalse($this->provider->supports(new User()));
    }

    public function testGetPreferredLocalization(): void
    {
        $localization = new Localization();
        $this->passwordResetRequestContext->setLocalization($localization);

        $customerUser = new CustomerUser();
        self::assertTrue($this->provider->supports($customerUser));
        self::assertSame($localization, $this->provider->getPreferredLocalization($customerUser));
    }

    public function testGetPreferredLocalizationWhenNotSupported(): void
    {
        $this->expectException(\LogicException::class);

        $this->provider->getPreferredLocalization(new CustomerUser());
    }
}
