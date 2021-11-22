<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\EventListener\FrontendSearchListener;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\SearchBundle\Event\PrepareEntityMapEvent;
use Oro\Bundle\SearchBundle\Event\SearchMappingCollectEvent;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Component\Testing\Unit\EntityTrait;

class FrontendSearchListenerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var FrontendSearchListener */
    protected $listener;

    /** @var OwnershipMetadataProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $metadataProvider;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->metadataProvider = $this->createMock(OwnershipMetadataProviderInterface::class);

        $this->listener = new FrontendSearchListener($this->metadataProvider);
    }

    public function testCollectEntityMapEvent()
    {
        $metadata = new FrontendOwnershipMetadata('FRONTEND_CUSTOMER', 'frontend_owner', 'frontend_owner_id');
        $this->metadataProvider->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadata);

        $event = new SearchMappingCollectEvent([
            CustomerAddress::class => [
                'alias' => 'oro_customer_address',
            ],
        ]);

        $this->listener->collectEntityMapEvent($event);

        $this->assertEquals(
            [
                CustomerAddress::class => [
                    'alias' => 'oro_customer_address',
                    'fields' => [
                        [
                            'name' => 'frontend_owner',
                            'target_type' => 'integer',
                            'target_fields' => ['oro_customer_address_frontend_owner'],
                        ],
                    ],
                ],
            ],
            $event->getMappingConfig()
        );
    }

    public function testPrepareEntityMapEventWithCustomer()
    {
        $entity = new CustomerAddress();
        $entity->setFrontendOwner($this->getEntity(Customer::class, ['id' => 10]));

        $metadata = new FrontendOwnershipMetadata('FRONTEND_CUSTOMER', 'frontend_owner', 'frontend_owner_id');
        $this->metadataProvider->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadata);

        $event = new PrepareEntityMapEvent($entity, get_class($entity), [], ['alias' => 'customer_address']);
        $this->listener->prepareEntityMapEvent($event);
        $resultData = $event->getData();

        $this->assertEquals(10, $resultData['integer']['customer_address_frontend_owner']);
    }

    public function testPrepareEntityMapEventWithCustomerUser()
    {
        $entity = new CustomerUserAddress();
        $entity->setFrontendOwner($this->getEntity(CustomerUser::class, ['id' => 15]));

        $metadata = new FrontendOwnershipMetadata('FRONTEND_USER', 'frontend_owner', 'frontend_owner_id');
        $this->metadataProvider->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadata);

        $event = new PrepareEntityMapEvent($entity, get_class($entity), [], ['alias' => 'customer_user_address']);
        $this->listener->prepareEntityMapEvent($event);
        $resultData = $event->getData();

        $this->assertEquals(15, $resultData['integer']['customer_user_address_frontend_owner']);
    }

    public function testPrepareEntityMapEventWithUnknownType()
    {
        $entity = new CustomerUserAddress();
        $entity->setFrontendOwner($this->getEntity(CustomerUser::class, ['id' => 20]));

        $metadata = new FrontendOwnershipMetadata('USER', 'frontend_owner', 'frontend_owner_id');
        $this->metadataProvider->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadata);

        $event = new PrepareEntityMapEvent($entity, get_class($entity), [], ['alias' => 'customer_user_address']);
        $this->listener->prepareEntityMapEvent($event);
        $resultData = $event->getData();

        $this->assertEquals([], $resultData);
    }

    public function testPrepareEntityMapEventWithoutMetadata()
    {
        $entity = new CustomerUserAddress();
        $entity->setFrontendOwner($this->getEntity(CustomerUser::class, ['id' => 20]));

        $this->metadataProvider->expects($this->once())
            ->method('getMetadata')
            ->willReturn(null);

        $event = new PrepareEntityMapEvent($entity, get_class($entity), [], ['alias' => 'customer_user_address']);
        $this->listener->prepareEntityMapEvent($event);
        $resultData = $event->getData();

        $this->assertEquals([], $resultData);
    }

    public function testPrepareEntityMapEventWithoutOwnerId()
    {
        $entity = new CustomerUserAddress();

        $metadata = new FrontendOwnershipMetadata('USER', 'frontend_owner', 'frontend_owner_id');
        $this->metadataProvider->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadata);

        $event = new PrepareEntityMapEvent($entity, get_class($entity), [], ['alias' => 'customer_user_address']);
        $this->listener->prepareEntityMapEvent($event);
        $resultData = $event->getData();

        $this->assertEquals([], $resultData);
    }
}
