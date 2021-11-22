<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Placeholder;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Placeholder\PlaceholderFilter;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;

class PlaceholderFilterTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|TokenAccessorInterface */
    private $tokenAccessor;

    /** @var PlaceholderFilter */
    private $placeholderFilter;

    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->placeholderFilter = new PlaceholderFilter($this->tokenAccessor);
    }

    /**
     * @dataProvider isUserApplicableDataProvider
     */
    public function testIsUserApplicable(object $user, bool $expected): void
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals($expected, $this->placeholderFilter->isUserApplicable());
    }

    public function isUserApplicableDataProvider(): array
    {
        return [
            [new \stdClass(), false],
            [new CustomerUser(), true]
        ];
    }

    /**
     * @dataProvider isLoginRequiredDataProvider
     */
    public function testIsLoginRequired(object|string $user, bool $expected): void
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals($expected, $this->placeholderFilter->isLoginRequired());
    }

    public function isLoginRequiredDataProvider(): array
    {
        return [
            ['none', true],
            [new CustomerUser(), false]
        ];
    }

    /**
     * @dataProvider isFrontendApplicableDataProvider
     */
    public function testIsFrontendApplicable(object|string $user, bool $expected): void
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals($expected, $this->placeholderFilter->isFrontendApplicable());
    }

    public function isFrontendApplicableDataProvider(): array
    {
        return [
            'anonymous' => ['none', true],
            'not valid user' => [new \stdClass(), false],
            'valid user' => [new CustomerUser(), true]
        ];
    }

    /**
     * @dataProvider isCustomerPageDataProvider
     */
    public function testIsCustomerPage(?object $entity, bool $expected): void
    {
        $this->assertEquals($expected, $this->placeholderFilter->isCustomerPage($entity));
    }

    public function isCustomerPageDataProvider(): array
    {
        return [
            'Not customer entity' => [
                'entity' => new User(),
                'expected' => false
            ],
            'Empty entity value' => [
                'entity' => null,
                'expected' => false
            ],
            'Customer entity usage' => [
                'entity' => new Customer(),
                'expected' => true
            ]
        ];
    }

    /**
     * @dataProvider isCustomerGroupPageDataProvider
     */
    public function testIsCustomerGroupPage(?object $entity, bool $expected): void
    {
        $this->assertEquals($expected, $this->placeholderFilter->isCustomerGroupPage($entity));
    }

    public function isCustomerGroupPageDataProvider(): array
    {
        return [
            'Not CustomerGroup entity' => [
                'entity' => new Customer(),
                'expected' => false
            ],
            'Empty entity value' => [
                'entity' => null,
                'expected' => false
            ],
            'CustomerGroup entity usage' => [
                'entity' => new CustomerGroup(),
                'expected' => true
            ]
        ];
    }
}
