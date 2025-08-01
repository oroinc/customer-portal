<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\Audit;
use Oro\Bundle\DataAuditBundle\Entity\AuditField;
use Oro\Bundle\DataAuditBundle\Exception\UnsupportedDataTypeException;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use PHPUnit\Framework\TestCase;

class AuditFieldTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors(): void
    {
        $properties = [
            ['id', 2],
            ['translationDomain', 'message']
        ];

        self::assertPropertyAccessors(new AuditField('field1', 'string', 'value', 'oldValue'), $properties);
    }

    public function testGetters(): void
    {
        $audit = new Audit();
        $auditField = new AuditField('field1', 'string', 'value', 'oldValue');
        $auditField->setAudit($audit);
        $auditField->setTranslationDomain('message');

        $this->assertSame($audit, $auditField->getAudit());
        $this->assertSame('field1', $auditField->getField());
        $this->assertSame('text', $auditField->getDataType());
        $this->assertSame('value', $auditField->getNewValue());
        $this->assertSame('oldValue', $auditField->getOldValue());
        $this->assertSame('message', $auditField->getTranslationDomain());
    }

    public function testUnsupportedType(): void
    {
        $this->expectException(UnsupportedDataTypeException::class);
        $this->expectExceptionMessage('Unsupported audit data type "string1"');

        new AuditField('field1', 'string1', 'value', 'oldValue');
    }
}
