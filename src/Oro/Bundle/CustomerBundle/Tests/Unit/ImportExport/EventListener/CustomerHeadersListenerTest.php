<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\ImportExport\EventListener;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\ImportExport\EventListener\CustomerHeadersListener;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\ImportExportBundle\Event\LoadEntityRulesAndBackendHeadersEvent;

class CustomerHeadersListenerTest extends \PHPUnit_Framework_TestCase
{
    const DELIMITER = ':';

    /**
     * @var FieldHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fieldHelper;

    /**
     * @var CustomerHeadersListener
     */
    private $listener;

    protected function setUp()
    {
        $this->fieldHelper = $this->createMock(FieldHelper::class);
        $this->listener = new CustomerHeadersListener($this->fieldHelper);
    }

    public function testAfterLoadEntityRulesAndBackendHeadersWhenNoCustomer()
    {
        $this->fieldHelper->expects($this->never())
            ->method('getConfigValue');

        $event = new LoadEntityRulesAndBackendHeadersEvent(
            \stdClass::class,
            [],
            [],
            self::DELIMITER,
            'type'
        );
        $this->listener->afterLoadEntityRulesAndBackendHeaders($event);
        $this->assertEmpty($event->getHeaders());
        $this->assertEmpty($event->getRules());
    }

    public function testAfterLoadEntityRulesAndBackendHeadersWhenExist()
    {
        $this->fieldHelper->expects($this->exactly(2))
            ->method('getConfigValue')
            ->willReturn(false);

        $headers = [
            [
                'value' => 'owner:id',
            ],
            [
                'value' => 'parent:id',
            ]
        ];
        $event = new LoadEntityRulesAndBackendHeadersEvent(
            Customer::class,
            $headers,
            [],
            self::DELIMITER,
            'type'
        );
        $this->listener->afterLoadEntityRulesAndBackendHeaders($event);
        $this->assertEquals($event->getHeaders(), $headers);
        $this->assertEmpty($event->getRules());
    }

    public function testAfterLoadEntityRulesAndBackendHeaders()
    {
        $this->fieldHelper->expects($this->exactly(2))
            ->method('getConfigValue')
            ->willReturn(false);

        $event = new LoadEntityRulesAndBackendHeadersEvent(
            Customer::class,
            [],
            [],
            self::DELIMITER,
            'type'
        );

        $expectedHeaders = [
            [
                'value' => 'owner:id',
                'order' => 50,
            ],
            [
                'value' => 'parent:id',
                'order' => 30,
            ]
        ];
        $expectedRules = [
            'Owner Id' => [
                'value' => 'owner:id',
                'order' => 50,
            ],
            'Parent Id' => [
                'value' => 'parent:id',
                'order' => 30,
            ],
        ];
        $this->listener->afterLoadEntityRulesAndBackendHeaders($event);
        $this->assertEquals($event->getHeaders(), $expectedHeaders);
        $this->assertEquals($event->getRules(), $expectedRules);
    }
}
