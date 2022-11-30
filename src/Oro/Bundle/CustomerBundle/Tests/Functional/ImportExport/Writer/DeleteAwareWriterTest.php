<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Writer;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\ImportExport\Writer\DeleteAwareWriter;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerAddresses;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class DeleteAwareWriterTest extends WebTestCase
{
    private DeleteAwareWriter $writer;
    private StepExecution $stepExecution;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerAddresses::class]);

        $jobExecution = new JobExecution();
        $jobExecution->setJobInstance(new JobInstance());
        $this->stepExecution = new StepExecution('step', $jobExecution);

        $this->writer = $this->getContainer()->get('oro_customer.importexport.delete_aware.writer');
        $this->writer->setStepExecution($this->stepExecution);
    }

    public function testWrite()
    {
        /** @var CustomerAddress $address */
        $address = $this->getReference('customer.level_1.address_1');
        $this->assertEquals('customer.level_1.address_1', $address->getLabel());

        $address->setLabel('label up');
        $this->writer->write([$address]);

        $em = $this->getContainer()->get('doctrine')->getManagerForClass(CustomerAddress::class);
        $address = $em->find(CustomerAddress::class, $address->getId());

        $this->assertEquals('label up', $address->getLabel());
    }

    public function testWriteRemove()
    {
        /** @var CustomerAddress $address */
        $address = $this->getReference('customer.level_1.address_1');

        $registry = $this->getContainer()->get('oro_importexport.context_registry');
        $context = $registry->getByStepExecution($this->stepExecution);
        $context->setValue('marked_for_removal', [$address->getId()]);
        $addressId = $address->getId();

        $this->writer->write([$address]);

        $em = $this->getContainer()->get('doctrine')->getManagerForClass(CustomerAddress::class);

        $this->assertNull($em->find(CustomerAddress::class, $addressId));
    }

    public function testWriteDuplicatePrimaryAndDuplicateDefaultTypes()
    {
        /** @var CustomerAddress $address */
        $address = $this->getReference('customer.level_1.address_1');
        $this->assertEquals('customer.level_1.address_1', $address->getLabel());

        $em = $this->getContainer()->get('doctrine')->getManagerForClass(CustomerAddress::class);
        $billingType = $em->find(AddressType::class, AddressType::TYPE_BILLING);
        $shippingType = $em->find(AddressType::class, AddressType::TYPE_SHIPPING);

        $newAddress1 = clone $address;
        $newAddress1->setStreet('street_example1');
        $newAddress1->setPrimary(true);
        $newAddress1->setDefaults(new ArrayCollection([$billingType, $shippingType]));
        $em->getClassMetadata(CustomerAddress::class)->setIdentifierValues($newAddress1, ['id' => null]);

        $newAddress2 = clone $address;
        $newAddress2->setStreet('street_example2');
        $newAddress2->setPrimary(true);
        $newAddress2->setTypes(new ArrayCollection([$billingType, $shippingType]));
        $newAddress2->setDefaults(new ArrayCollection([$billingType, $shippingType]));
        $em->getClassMetadata(CustomerAddress::class)->setIdentifierValues($newAddress2, ['id' => null]);

        $this->writer->write([$newAddress1, $newAddress2]);

        /** @var CustomerAddress $address */
        $address = $em->find(CustomerAddress::class, $address->getId());
        $this->assertFalse($address->isPrimary());
        $this->assertFalse($address->hasDefault('billing'));
        $this->assertFalse($address->hasDefault('shipping'));

        /** @var CustomerAddress $existingAddress2 */
        $existingAddress2 = $em->find(
            CustomerAddress::class,
            $this->getReference('customer.level_1.address_2')->getId()
        );
        $this->assertFalse($existingAddress2->isPrimary());
        $this->assertFalse($existingAddress2->hasDefault('billing'));
        $this->assertFalse($existingAddress2->hasDefault('shipping'));

        /** @var CustomerAddress $newAddress1 */
        $newAddress1 = $em->find(CustomerAddress::class, $newAddress1->getId());
        $this->assertFalse($newAddress1->isPrimary());
        $this->assertFalse($newAddress1->hasDefault('billing'));
        $this->assertFalse($newAddress1->hasDefault('shipping'));

        /** @var CustomerAddress $newAddress2 */
        $newAddress2 = $em->find(CustomerAddress::class, $newAddress2->getId());
        $this->assertTrue($newAddress2->isPrimary());
        $this->assertTrue($newAddress2->hasDefault('billing'));
        $this->assertTrue($newAddress2->hasDefault('shipping'));
    }
}
