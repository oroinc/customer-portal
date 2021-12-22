<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Async;

use Oro\Bundle\CustomerBundle\Async\OwnerTreeCacheJobProcessor;
use Oro\Bundle\CustomerBundle\Async\Topics;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Model\BusinessUnitMessageFactory;
use Oro\Bundle\CustomerBundle\Model\OwnerTreeMessageFactory;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobProcessor;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;

/**
 * @dbIsolationPerTest
 */
class OwnerTreeCacheJobProcessorTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initClient();
        $this->loadFixtures([LoadCustomerUserData::class]);
    }

    public function testProcessWithCustomerUsersToWarmUpCache(): void
    {
        $processor = $this->getOwnerTreeCacheJobProcessor();
        $messageData = $this->getOwnerTreeMessageFactory()->createMessage(1000);

        $message = new Message();
        $message->setMessageId('test_message');
        $message->setBody(JSON::encode($messageData));

        $this->updateCustomerUserLastLogin(LoadCustomerUserData::LEVEL_1_EMAIL, 100);
        $this->updateCustomerUserLastLogin(LoadCustomerUserData::LEVEL_1_1_EMAIL, 1100);
        $this->updateCustomerUserLastLogin(LoadCustomerUserData::GROUP2_EMAIL, 800);

        $result = $processor->process($message, $this->createMock(SessionInterface::class));

        $this->assertMessagesCount(Topics::CALCULATE_BUSINESS_UNIT_OWNER_TREE_CACHE, 2);
        $this->assertMessageSentForCustomer($this->getReference(LoadCustomers::CUSTOMER_LEVEL_1));
        $this->assertMessageSentForCustomer($this->getReference(LoadCustomers::CUSTOMER_LEVEL_1_DOT_2));

        $this->assertEquals(MessageProcessorInterface::ACK, $result);
    }

    public function testProcessWithNoCustomerUsersToWarmUpCache(): void
    {
        $processor = $this->getOwnerTreeCacheJobProcessor();
        $messageData = $this->getOwnerTreeMessageFactory()->createMessage(1000);

        $message = new Message();
        $message->setMessageId('test_message');
        $message->setBody(JSON::encode($messageData));

        $result = $processor->process($message, $this->createMock(SessionInterface::class));

        $this->assertMessagesEmpty(Topics::CALCULATE_BUSINESS_UNIT_OWNER_TREE_CACHE);

        $this->assertEquals(MessageProcessorInterface::ACK, $result);
    }

    private function assertMessageSentForCustomer(Customer $customer)
    {
        $rootJob = $this->getJobProcessor()->findOrCreateRootJob('test_message', Topics::CALCULATE_OWNER_TREE_CACHE);
        $childJob = $this->getJobProcessor()->findOrCreateChildJob(
            sprintf(
                '%s:%s:%s',
                Topics::CALCULATE_OWNER_TREE_CACHE,
                Customer::class,
                $customer->getId()
            ),
            $rootJob
        );

        $this->assertMessageSent(
            Topics::CALCULATE_BUSINESS_UNIT_OWNER_TREE_CACHE,
            $this->getBusinessUnitMessageFactory()
                ->createMessage($childJob->getId(), Customer::class, $customer->getId())
        );
    }

    private function getJobProcessor(): JobProcessor
    {
        return $this->getContainer()->get('oro_message_queue.job.processor');
    }

    private function getOwnerTreeCacheJobProcessor(): OwnerTreeCacheJobProcessor
    {
        return $this->getContainer()->get('oro_customer.tests.async.owner_tree_cache_job_processor');
    }

    private function getOwnerTreeMessageFactory(): OwnerTreeMessageFactory
    {
        return $this->getContainer()->get('oro_customer.tests.model.owner_tree_message_factory');
    }

    private function getBusinessUnitMessageFactory(): BusinessUnitMessageFactory
    {
        return $this->getContainer()->get('oro_customer.tests.model.business_unit_message_factory');
    }

    private function updateCustomerUserLastLogin(string $reference, int $secondsAgo): void
    {
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $customerUser = $this->getReference($reference);
        $date = (new \DateTime());
        $date->sub(new \DateInterval(sprintf('PT%dS', $secondsAgo)));

        $customerUser->setLastLogin((new \DateTime())->sub(new \DateInterval(sprintf('PT%dS', $secondsAgo))));

        $entityManager->persist($customerUser);
        $entityManager->flush();
    }
}
