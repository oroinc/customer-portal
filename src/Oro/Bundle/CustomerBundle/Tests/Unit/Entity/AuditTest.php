<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\Audit;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataAuditBundle\Entity\AuditField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class AuditTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testUser()
    {
        $user = new CustomerUser();
        $audit = new Audit();
        $audit->setUser($user);
        $this->assertSame($user, $audit->getUser());
    }

    public function testAccessors()
    {
        $properties = [
            ['objectName', (string)(new CustomerUser())],
            ['objectId', 2],
            ['organization', new Organization()],
            ['id', 2],
            ['action', 'some_action'],
            ['version', 1],
        ];

        static::assertPropertyAccessors(new Audit(), $properties);
    }

    public function testLoggedAt()
    {
        $audit = new Audit();
        $audit->setLoggedAt();
        $this->assertInstanceOf('\DateTime', $audit->getLoggedAt());
    }

    public function testFields()
    {
        $audit = new Audit();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $audit->getFields());

        $audit->addField(new AuditField('field1', 'string', 'a', 'b'));
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $audit->getFields());

        $audit->getFields()->map(
            function ($field) {
                $this->assertInstanceOf('Oro\Bundle\DataAuditBundle\Entity\AuditField', $field);
            }
        );

        $this->assertInstanceOf('Oro\Bundle\DataAuditBundle\Entity\AuditField', $audit->getField('field1'));
        $this->assertFalse($audit->getField('field2'));

        $audit->addField(new AuditField('field1', 'string', 'a2', 'b2'));
        $this->assertEquals('a2', $audit->getField('field1')->getNewValue());
        $this->assertEquals('b2', $audit->getField('field1')->getOldValue());
    }
}
