<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Model;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Model\BusinessUnitMessageFactory;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ProductBundle\Model\Exception\InvalidArgumentException;

class BusinessUnitMessageFactoryTest extends \PHPUnit\Framework\TestCase
{
    private const JOB_ID = 7;
    private const ENTITY_CLASS = 'EntityClass';

    /**
     * @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $doctrineHelper;

    /**
     * @var BusinessUnitMessageFactory
     */
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

        $this->doctrineHelper->expects($this->never())
            ->method('getEntityReference');

        $this->assertEquals(self::JOB_ID, $this->messageFactory->getJobIdFromMessage($messageData));
    }

    public function entityIdDataProvider(): array
    {
        return [
            'integer entity id' => [
                'entityId' => 4545,
            ],
            'string entity id' => [
                'entityId' => 'someEntityId',
            ]
        ];
    }

    /**
     * @dataProvider entityIdDataProvider
     * @param int|string $entityId
     */
    public function testGetBusinessUnitFromMessage($entityId): void
    {
        $messageData = $this->messageFactory->createMessage(self::JOB_ID, self::ENTITY_CLASS, $entityId);
        $entity = new Customer();

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityReference')
            ->with(self::ENTITY_CLASS, $entityId)
            ->willReturn($entity);

        $this->assertEquals($entity, $this->messageFactory->getBusinessUnitFromMessage($messageData));
    }

    /**
     * @dataProvider wrongEntityIdDataProvider
     * @param mixed $entityId
     */
    public function testCreateMessageWithWrongEntityIdType($entityId): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The option "entityId" with value stdClass is expected to be of type "int" or "string"'
        );

        $this->messageFactory->createMessage(self::JOB_ID, self::ENTITY_CLASS, $entityId);
    }

    public function wrongEntityIdDataProvider(): array
    {
        return [
            'entity id wrong type' => [
                'entityId' => new \stdClass(),
            ]
        ];
    }
}
