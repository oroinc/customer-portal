<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\ImportExport\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\ImportExport\EventListener\CustomerUserHeadersListener;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\ImportExportBundle\Event\LoadEntityRulesAndBackendHeadersEvent;

class CustomerUserHeadersListenerTest extends \PHPUnit_Framework_TestCase
{
    const DELIMITER = ':';

    /**
     * @var FieldHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fieldHelper;

    /**
     * @var CustomerUserHeadersListener
     */
    private $listener;

    protected function setUp()
    {
        $this->fieldHelper = $this->createMock(FieldHelper::class);
        $this->listener = new CustomerUserHeadersListener($this->fieldHelper);
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
                'value' => 'customer:name',
            ]
        ];
        $event = new LoadEntityRulesAndBackendHeadersEvent(
            CustomerUser::class,
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
            CustomerUser::class,
            [],
            [],
            self::DELIMITER,
            'type'
        );

        $expectedHeaders = [
            [
                'value' => 'owner:id',
                'order' => 80,
            ],
            [
                'value' => 'customer:name',
                'order' => 40,
            ]
        ];
        $expectedRules = [
            'Owner Id' => [
                'value' => 'owner:id',
                'order' => 80,
            ],
            'Customer Name' => [
                'value' => 'customer:name',
                'order' => 40,
            ],
        ];
        $this->listener->afterLoadEntityRulesAndBackendHeaders($event);
        $this->assertEquals($event->getHeaders(), $expectedHeaders);
        $this->assertEquals($event->getRules(), $expectedRules);
    }
}
