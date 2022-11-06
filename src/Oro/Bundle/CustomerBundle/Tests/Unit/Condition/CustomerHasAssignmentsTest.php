<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Condition;

use Oro\Bundle\CustomerBundle\Condition\CustomerHasAssignments;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Handler\CustomerAssignHelper;
use Oro\Component\ConfigExpression\Exception\InvalidArgumentException;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class CustomerHasAssignmentsTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var CustomerAssignHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $helper;

    /** @var CustomerHasAssignments */
    private $condition;

    protected function setUp(): void
    {
        $this->helper = $this->createMock(CustomerAssignHelper::class);

        $this->condition = new CustomerHasAssignments($this->helper);
    }

    public function testGetName()
    {
        $this->assertEquals(CustomerHasAssignments::NAME, $this->condition->getName());
    }

    /**
     * @dataProvider evaluateDataProvider
     */
    public function testEvaluateCustomerWithId(bool $hasAssignments, bool $expected)
    {
        $customer = $this->getEntity(Customer::class, ['id' => 42]);

        $this->helper->expects($this->once())
            ->method('hasAssignments')
            ->with($customer)
            ->willReturn($hasAssignments);

        $this->assertSame($this->condition, $this->condition->initialize(['customer' => $customer]));
        $this->assertEquals($expected, $this->condition->evaluate([]));
    }

    public function evaluateDataProvider(): array
    {
        return [
            ['hasAssignments' => true, 'expected' => true],
            ['hasAssignments' => false, 'expected' => false],
        ];
    }

    public function testEvaluateCustomerWithoutId()
    {
        $this->helper->expects($this->never())
            ->method($this->anything());

        $this->assertSame($this->condition, $this->condition->initialize(['customer' => new Customer()]));
        $this->assertFalse($this->condition->evaluate([]));
    }

    /**
     * @dataProvider initializeExceptionProvider
     */
    public function testInitializeException(array $options, string $exception, string $exceptionMessage)
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);

        $this->condition->initialize($options);
    }

    public function initializeExceptionProvider(): array
    {
        return [
            [
                'options' => [],
                'exception' => InvalidArgumentException::class,
                'exceptionMessage' => 'Customer parameter is required'
            ],
            [
                'options' => ['customer' => new \stdClass()],
                'exception' => InvalidArgumentException::class,
                'exceptionMessage' => sprintf(
                    'Customer parameter should be instance of %s or %s',
                    Customer::class,
                    PropertyPathInterface::class
                )
            ]
        ];
    }
}
