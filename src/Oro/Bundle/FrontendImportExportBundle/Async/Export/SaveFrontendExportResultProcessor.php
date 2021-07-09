<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async\Export;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendImportExportBundle\Async\Topics;
use Oro\Bundle\FrontendImportExportBundle\Manager\ExportResultNotificationSender;
use Oro\Bundle\FrontendImportExportBundle\Manager\FrontendImportExportResultManager;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobProcessor;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Processes saving import/export result data.
 */
class SaveFrontendExportResultProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ManagerRegistry $managerRegistry;

    private FrontendImportExportResultManager $importExportResultManager;

    private JobProcessor $jobProcessor;

    private ExportResultNotificationSender $exportResultNotificationSender;

    public function __construct(
        ManagerRegistry $managerRegistry,
        FrontendImportExportResultManager $importExportResultManager,
        JobProcessor $jobProcessor,
        ExportResultNotificationSender $exportResultNotificationSender
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->importExportResultManager = $importExportResultManager;
        $this->jobProcessor = $jobProcessor;
        $this->exportResultNotificationSender = $exportResultNotificationSender;
        $this->logger = new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics(): array
    {
        return [Topics::SAVE_EXPORT_RESULT];
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $body = JSON::decode($message->getBody());

        try {
            $input = $this->resolveInput($body);
        } catch (MissingOptionsException | UndefinedOptionsException | InvalidOptionsException $e) {
            $this->logger->error(
                sprintf('Error occurred during save result: %s', $e->getMessage()),
                ['exception' => $e]
            );

            return self::REJECT;
        }

        $job = $this->jobProcessor->findJobById((int)$input['jobId']);
        if (!$job) {
            $this->logger->error(sprintf('Job #%d was not found', $input['jobId']));

            return self::REJECT;
        }

        $customerUser = $this->managerRegistry
            ->getManagerForClass(CustomerUser::class)
            ->find(CustomerUser::class, $input['customerUserId']);
        if (!$customerUser) {
            $this->logger->error(sprintf('Customer user with id #%d was not found', $input['customerUserId']));

            return self::REJECT;
        }

        $rootJob = $job->isRoot() ? $job : $job->getRootJob();

        try {
            $jobData = $rootJob->getData();
            $frontendImportExportResult = $this->importExportResultManager->saveResult(
                $rootJob->getId(),
                $input['type'],
                $input['entity'],
                $customerUser,
                $jobData['file'] ?? null,
                $input['options']
            );
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Unhandled error save result: %s', $e->getMessage()),
                ['exception' => $e]
            );

            return self::REJECT;
        }

        $emailUsers = $this->exportResultNotificationSender->sendEmailNotification($frontendImportExportResult);
        if (!$emailUsers) {
            $this->logger->error(
                sprintf(
                    'Failed to send the export result notification email for customer user with id #%d',
                    $input['customerUserId']
                )
            );

            return self::REJECT;
        }

        return self::ACK;
    }

    private function resolveInput(array $options = []): array
    {
        $optionResolver = new OptionsResolver();
        $optionResolver->setRequired('jobId')->setAllowedTypes('jobId', 'int');
        $optionResolver->setRequired('entity')->setAllowedTypes('entity', 'string');
        $optionResolver->setRequired('type')->setAllowedValues('type', [ProcessorRegistry::TYPE_EXPORT]);
        $optionResolver->setDefined('refererUrl')->setDefault('refererUrl', '');
        $optionResolver->setRequired('customerUserId')
            ->setAllowedTypes('customerUserId', 'int');

        $optionResolver->setDefined('options')
            ->setAllowedTypes('options', ['array'])
            ->setDefault('options', []);

        return $optionResolver->resolve($options);
    }
}
