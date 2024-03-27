<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\WebsiteBundle\EventListener\Doctrine\EmailTemplateMetadataListener;
use PHPUnit\Framework\TestCase;

class EmailTemplateMetadataListenerTest extends TestCase
{
    private EmailTemplateMetadataListener $listener;

    protected function setUp(): void
    {
        $this->listener = new EmailTemplateMetadataListener();
    }

    public function testLoadClassMetadataWhenNotEmailTemplate(): void
    {
        $classMetadata = new ClassMetadataInfo(\stdClass::class);
        $classMetadata->table['uniqueConstraints'] = [];
        $event = new LoadClassMetadataEventArgs($classMetadata, $this->createMock(ObjectManager::class));

        $this->listener->loadClassMetadata($event);

        self::assertEmpty($classMetadata->table['uniqueConstraints']);
    }

    public function testLoadClassMetadataWhenEmailTemplate(): void
    {
        $classMetadata = new ClassMetadataInfo(EmailTemplate::class);
        $classMetadata->table['uniqueConstraints'] = ['UQ_NAME' => ['columns' => ['name', 'entityName']]];

        $event = new LoadClassMetadataEventArgs($classMetadata, $this->createMock(ObjectManager::class));

        $this->listener->loadClassMetadata($event);

        self::assertEquals(
            ['UQ_NAME' => ['columns' => ['name', 'entityName', 'website_id']]],
            $classMetadata->table['uniqueConstraints']
        );
    }
}
