<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Placeholder;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Placeholder\PlaceholderFilter;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

class PlaceholderFilterTest extends \PHPUnit\Framework\TestCase
{
    /** @var PlaceholderFilter */
    protected $placeholderFilter;

    /** @var \PHPUnit\Framework\MockObject\MockObject|TokenAccessorInterface */
    protected $tokenAccessor;

    protected function setUp()
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->placeholderFilter = new PlaceholderFilter($this->tokenAccessor);
    }

    protected function tearDown()
    {
        unset($this->placeholderFilter, $this->tokenAccessor);
    }

    /**
     * @dataProvider isUserApplicableDataProvider
     *
     * @param object $user
     * @param bool $expected
     */
    public function testIsUserApplicable($user, $expected)
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals($expected, $this->placeholderFilter->isUserApplicable());
    }

    /**
     * @return array
     */
    public function isUserApplicableDataProvider()
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
    public function testIsLoginRequired($user, $expected)
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals($expected, $this->placeholderFilter->isLoginRequired());
    }

    /**
     * @return array
     */
    public function isLoginRequiredDataProvider()
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
    public function testIsFrontendApplicable($user, $expected)
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals($expected, $this->placeholderFilter->isFrontendApplicable());
    }

    /**
     * @return array
     */
    public function isFrontendApplicableDataProvider()
    {
        return [
            'anonymous' => ['none', true],
            'not valid user' => [new \stdClass(), false],
            'valid user' => [new CustomerUser(), true]
        ];
    }
}
