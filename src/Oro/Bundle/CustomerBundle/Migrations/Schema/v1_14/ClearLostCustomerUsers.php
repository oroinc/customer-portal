<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_14;

use Doctrine\DBAL\Connection;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\NativeQueryExecutorHelper;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;

/**
 * Deletes customer users without assigned customer
 */
class ClearLostCustomerUsers implements MessageProcessorInterface
{
    /**
     *  The topic name declared at processor because this is disposable topic that will be used
     *  only during the system update.
     */
    const TOPIC_NAME = 'oro_customer.clear_lost_customer_users';
    const BATCH_SIZE = 200;

    /** @var MessageProducerInterface */
    protected $messageProducer;

    /** @var NativeQueryExecutorHelper */
    protected $queryHelper;

    public function __construct(
        MessageProducerInterface $messageProducer,
        NativeQueryExecutorHelper $queryHelper
    ) {
        $this->messageProducer = $messageProducer;
        $this->queryHelper = $queryHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        if ($message->getBody() !== '') {
            // we have page number we should process, so now process this page
            $body = json_decode($message->getBody(), true);
            $this->processBatch((int)$body['batch_number']);
        } else {
            // we have no page number we should process, so now split work to batches
            $this->scheduleMigrateProcesses();
        }

        return self::ACK;
    }

    /**
     * Split work to batches
     */
    protected function scheduleMigrateProcesses()
    {
        /** @var Connection $connection */
        $connection = $this->queryHelper->getManager(CustomerUser::class)->getConnection();
        $maxItemNumber = $connection
            ->fetchColumn(
                sprintf(
                    'select max(id) from %s',
                    $this->queryHelper->getTableName(CustomerUser::class)
                )
            );
        $jobsCount = floor((int)$maxItemNumber / self::BATCH_SIZE);
        for ($i = 0; $i <= $jobsCount; $i++) {
            $this->messageProducer->send(self::TOPIC_NAME, json_encode(['batch_number' => $i]));
        }
    }

    /**
     * Process one data batch
     *
     * @param integer $pageNumber
     */
    protected function processBatch($pageNumber)
    {
        $startId = self::BATCH_SIZE * $pageNumber;
        $endId = $startId + self::BATCH_SIZE - 1;

        /** @var Connection $connection */
        $em = $this->queryHelper->getManager(CustomerUser::class);

        // we should use ORM because of listeners that works with ORM
        $customers = $em->getRepository(CustomerUser::class)->createQueryBuilder('customer')
            ->select('customer')
            ->where('customer.id BETWEEN :startId AND :endID')
            ->andWhere('customer.customer is null')
            ->setParameter('startId', $startId)
            ->setParameter('endID', $endId)
            ->getQuery()
            ->getResult();

        if (empty($customers)) {
            return;
        }

        /** @var CustomerUser $customer */
        foreach ($customers as $customer) {
            $em->remove($customer);
        }

        $em->flush();
    }
}
