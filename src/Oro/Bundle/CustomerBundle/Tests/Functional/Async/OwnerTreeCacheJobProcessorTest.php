<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Async;

use Oro\Bundle\CustomerBundle\Async\Topic\CustomerCalculateOwnerTreeCacheByBusinessUnitTopic;
use Oro\Bundle\CustomerBundle\Async\Topic\CustomerCalculateOwnerTreeCacheTopic;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Model\BusinessUnitMessageFactory;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\MessageQueueBundle\Test\Functional\JobsAwareTestTrait;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;

/**
 * @dbIsolationPerTest
 */
class OwnerTreeCacheJobProcessorTest extends WebTestCase
{
    use MessageQueueExtension;
    use JobsAwareTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initClient();
        $this->loadFixtures([LoadCustomerUserData::class]);
    }

    public function testProcessWithCustomerUsersToWarmUpCache(): void
    {
        self::assertUniqueJobsProcessed();

        $this->updateCustomerUserLastLogin(LoadCustomerUserData::LEVEL_1_EMAIL, 100);
        $this->updateCustomerUserLastLogin(LoadCustomerUserData::LEVEL_1_1_EMAIL, 1100);
        $this->updateCustomerUserLastLogin(LoadCustomerUserData::GROUP2_EMAIL, 800);

        $sentMessage = self::sendMessage(
            CustomerCalculateOwnerTreeCacheTopic::getName(),
            [CustomerCalculateOwnerTreeCacheTopic::CACHE_TTL => 1000]
        );

        self::consume();

        self::assertProcessedMessageStatus(MessageProcessorInterface::ACK, $sentMessage);
        self::assertProcessedMessageProcessor('oro_customer.async.owner_tree_cache_job_processor', $sentMessage);

        self::assertMessagesCount(CustomerCalculateOwnerTreeCacheByBusinessUnitTopic::getName(), 2);
        $this->assertMessageSentForCustomer(
            $sentMessage->getMessageId(),
            $this->getReference(LoadCustomers::CUSTOMER_LEVEL_1)
        );
        $this->assertMessageSentForCustomer(
            $sentMessage->getMessageId(),
            $this->getReference(LoadCustomers::CUSTOMER_LEVEL_1_DOT_2)
        );
    }

    public function testProcessWithNoCustomerUsersToWarmUpCache(): void
    {
        self::assertUniqueJobsProcessed();

        $sentMessage = self::sendMessage(
            CustomerCalculateOwnerTreeCacheTopic::getName(),
            [CustomerCalculateOwnerTreeCacheTopic::CACHE_TTL => 1000]
        );
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::ACK, $sentMessage);
        self::assertProcessedMessageProcessor('oro_customer.async.owner_tree_cache_job_processor', $sentMessage);

        self::assertMessagesEmpty(CustomerCalculateOwnerTreeCacheByBusinessUnitTopic::getName());
    }

    private function assertMessageSentForCustomer(string $messageId, Customer $customer): void
    {
        $rootJob = $this->getJobProcessor()->findOrCreateRootJob(
            $messageId,
            CustomerCalculateOwnerTreeCacheTopic::getName()
        );
        $childJob = $this->getJobProcessor()->findOrCreateChildJob(
            sprintf(
                '%s:%s:%s',
                CustomerCalculateOwnerTreeCacheTopic::getName(),
                Customer::class,
                $customer->getId()
            ),
            $rootJob
        );

        self::assertMessageSent(
            CustomerCalculateOwnerTreeCacheByBusinessUnitTopic::getName(),
            $this->getBusinessUnitMessageFactory()
                ->createMessage($childJob->getId(), Customer::class, $customer->getId())
        );
    }

    private function getBusinessUnitMessageFactory(): BusinessUnitMessageFactory
    {
        return self::getContainer()->get('oro_customer.tests.model.business_unit_message_factory');
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
