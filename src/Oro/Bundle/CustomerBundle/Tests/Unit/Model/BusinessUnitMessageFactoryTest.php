<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Model;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Model\BusinessUnitMessageFactory;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class BusinessUnitMessageFactoryTest extends \PHPUnit\Framework\TestCase
{
    private const JOB_ID = 7;
    private const ENTITY_CLASS = 'EntityClass';

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var BusinessUnitMessageFactory */
    private $messageFactory;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->messageFactory = new BusinessUnitMessageFactory($this->doctrineHelper);
    }

    /**
     * @dataProvider entityIdDataProvider
     */
    public function testGetJobIdFromMessage($entityId): void
    {
        $messageData = $this->messageFactory->createMessage(self::JOB_ID, self::ENTITY_CLASS, $entityId);

        $this->doctrineHelper->expects(self::never())
            ->method('getEntityReference');

        self::assertEquals(self::JOB_ID, $this->messageFactory->getJobIdFromMessage($messageData));
    }

    public function entityIdDataProvider(): array
    {
        return [
            'integer entity id' => [
                'entityId' => 4545,
            ],
            'string entity id' => [
                'entityId' => 'someEntityId',
            ],
        ];
    }

    /**
     * @dataProvider entityIdDataProvider
     */
    public function testGetBusinessUnitFromMessage(int|string $entityId): void
    {
        $messageData = $this->messageFactory->createMessage(self::JOB_ID, self::ENTITY_CLASS, $entityId);
        $entity = new Customer();

        $repo = $this->createMock(EntityRepository::class);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityRepository')
            ->with(self::ENTITY_CLASS)
            ->willReturn($repo);

        $repo->expects(self::once())
            ->method('find')
            ->with($entityId)
            ->willReturn($entity);

        self::assertEquals($entity, $this->messageFactory->getBusinessUnitFromMessage($messageData));
    }
}
