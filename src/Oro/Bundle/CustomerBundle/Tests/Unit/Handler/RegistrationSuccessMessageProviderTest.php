<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Handler\RegistrationSuccessMessageProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RegistrationSuccessMessageProviderTest extends TestCase
{
    private CustomerUserManager&MockObject $customerUserManager;
    private RegistrationSuccessMessageProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->customerUserManager = $this->createMock(CustomerUserManager::class);
        $this->provider = new RegistrationSuccessMessageProvider($this->customerUserManager);
    }

    /**
     * @dataProvider getRegistrationSuccessMessageDataProvider
     */
    public function testGetRegistrationSuccessMessage(bool $isConfirmationRequired, string $expectedMessage): void
    {
        $this->customerUserManager->expects(self::once())
            ->method('isConfirmationRequired')
            ->willReturn($isConfirmationRequired);

        self::assertSame($expectedMessage, $this->provider->getRegistrationSuccessMessage());
    }

    public function getRegistrationSuccessMessageDataProvider(): array
    {
        return [
            'confirmation is not required' => [
                'isConfirmationRequired' => false,
                'expectedMessage' => 'oro.customer.controller.customeruser.registered.message',
            ],
            'confirmation is required' => [
                'isConfirmationRequired' => true,
                'expectedMessage' => 'oro.customer.controller.customeruser.registered_with_confirmation.message',
            ],
        ];
    }
}
