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
    protected PlaceholderFilter $placeholderFilter;

    /** @var \PHPUnit\Framework\MockObject\MockObject|TokenAccessorInterface */
    protected TokenAccessorInterface $tokenAccessor;

    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->placeholderFilter = new PlaceholderFilter($this->tokenAccessor);
    }

    protected function tearDown(): void
    {
        unset($this->placeholderFilter, $this->tokenAccessor);
    }

    /**
     * @dataProvider isUserApplicableDataProvider
     *
     * @param object $user
     * @param bool $expected
     */
    public function testIsUserApplicable($user, bool $expected): void
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
     *
     * @param mixed $user
     * @param bool $expected
     */
    public function testIsLoginRequired($user, bool $expected): void
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
     *
     * @param object|string $user
     * @param bool $expected
     */
    public function testIsFrontendApplicable($user, bool $expected): void
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
     * @param mixed $entity
     * @param bool $expected
     */
    public function testIsCustomerPage($entity, bool $expected): void
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
     * @param mixed $entity
     * @param bool $expected
     */
    public function testIsCustomerGroupPage($entity, bool $expected): void
    {
        $this->assertEquals($expected, $this->placeholderFilter->isCustomerGroupPage($entity));
    }

    /**
     * @return array
     */
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
