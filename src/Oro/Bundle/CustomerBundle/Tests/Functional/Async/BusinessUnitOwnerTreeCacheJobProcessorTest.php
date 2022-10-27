<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Async;

use Oro\Bundle\CustomerBundle\Async\Topic\CustomerCalculateOwnerTreeCacheByBusinessUnitTopic;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Model\BusinessUnitMessageFactory;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\MessageQueueBundle\Test\Functional\JobsAwareTestTrait;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\Job;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @dbIsolationPerTest
 */
class BusinessUnitOwnerTreeCacheJobProcessorTest extends WebTestCase
{
    use MessageQueueExtension;
    use JobsAwareTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initClient();
        $this->loadFixtures([LoadCustomerUserData::class]);
    }

    public function testProcessWhenNotFound(): void
    {
        $childJob = $this->createDelayedJob();
        $message = $this->getBusinessUnitMessageFactory()->createMessage(
            $childJob->getId(),
            Customer::class,
            PHP_INT_MAX
        );
        $sentMessage = self::sendMessage(
            CustomerCalculateOwnerTreeCacheByBusinessUnitTopic::getName(),
            $message
        );
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::REJECT, $sentMessage);
        self::assertProcessedMessageProcessor(
            'oro_customer.async.business_unit_owner_tree_cache_job_processor',
            $sentMessage
        );

        self::assertEquals(Job::STATUS_FAILED, $this->getJobProcessor()->findJobById($childJob->getId())->getStatus());

        self::assertTrue(
            self::getLoggerTestHandler()->hasError(
                sprintf('Business unit entity %s #%s is not found', Customer::class, PHP_INT_MAX)
            )
        );
    }

    public function testProcess(): void
    {
        /** @var Customer $customer */
        $customer = $this->getReference(LoadCustomers::CUSTOMER_LEVEL_1);

        /** @var CacheItemPoolInterface $cache */
        $cache = self::getContainer()->get('oro_customer.owner.frontend_ownership_tree_provider.cache');
        self::assertFalse($cache->hasItem('data_' . $customer->getId()));

        $this->updateCustomerUserLastLogin(LoadCustomerUserData::LEVEL_1_EMAIL, 100);

        $childJob = $this->createDelayedJob();
        $message = $this->getBusinessUnitMessageFactory()->createMessage(
            $childJob->getId(),
            Customer::class,
            $customer->getId()
        );
        $sentMessage = self::sendMessage(
            CustomerCalculateOwnerTreeCacheByBusinessUnitTopic::getName(),
            $message
        );
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::ACK, $sentMessage);
        self::assertProcessedMessageProcessor(
            'oro_customer.async.business_unit_owner_tree_cache_job_processor',
            $sentMessage
        );

        self::assertEquals(Job::STATUS_SUCCESS, $this->getJobProcessor()->findJobById($childJob->getId())->getStatus());

        self::assertTrue($cache->hasItem('data_' . $customer->getId()));
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
