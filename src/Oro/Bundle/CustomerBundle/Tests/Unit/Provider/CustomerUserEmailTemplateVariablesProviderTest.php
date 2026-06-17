<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserEmailTemplateVariablesProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CustomerUserEmailTemplateVariablesProviderTest extends TestCase
{
    private const TRANSLATED_LABEL = 'Full Name';

    private TranslatorInterface&MockObject $translator;
    private CustomerUserEmailTemplateVariablesProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->provider = new CustomerUserEmailTemplateVariablesProvider($this->translator);
    }

    public function testGetVariableDefinitions(): void
    {
        $this->translator
            ->expects(self::once())
            ->method('trans')
            ->with('oro.customer.customeruser.full_name')
            ->willReturn(self::TRANSLATED_LABEL);

        $expected = [
            CustomerUser::class => [
                'fullName' => [
                    'type'  => 'string',
                    'label' => self::TRANSLATED_LABEL,
                ],
            ],
        ];

        self::assertEquals($expected, $this->provider->getVariableDefinitions());
    }

    public function testGetVariableGetters(): void
    {
        $expected = [
            CustomerUser::class => [
                'fullName' => 'getFullName',
            ],
        ];

        self::assertEquals($expected, $this->provider->getVariableGetters());
    }

    public function testGetVariableProcessors(): void
    {
        self::assertEquals([], $this->provider->getVariableProcessors(CustomerUser::class));
        self::assertEquals([], $this->provider->getVariableProcessors('SomeOtherClass'));
    }
}
