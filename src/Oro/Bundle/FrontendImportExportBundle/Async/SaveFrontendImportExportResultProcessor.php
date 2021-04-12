<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async;

use Oro\Bundle\FrontendImportExportBundle\Manager\FrontendImportExportResultManager;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\Job;
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
class SaveFrontendImportExportResultProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    private FrontendImportExportResultManager $importExportResultManager;
    private JobProcessor $jobProcessor;

    /**
     * @param FrontendImportExportResultManager $importExportResultManager
     * @param UserManager $userManager
     * @param JobProcessor $jobProcessor
     */
    public function __construct(
        FrontendImportExportResultManager $importExportResultManager,
        JobProcessor $jobProcessor
    ) {
        $this->importExportResultManager = $importExportResultManager;
        $this->jobProcessor = $jobProcessor;
        $this->logger = new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $body = JSON::decode($message->getBody());

        try {
            $options = $this->configureOption($body);
        } catch (MissingOptionsException | UndefinedOptionsException | InvalidOptionsException $e) {
            $this->logger->critical(
                sprintf('Error occurred during save result: %s', $e->getMessage()),
                ['exception' => $e]
            );
            return self::REJECT;
        }

        $job = $this->jobProcessor->findJobById((int)$options['jobId']);
        $job = $job->isRoot() ? $job : $job->getRootJob();

        try {
            $this->saveJobResult($job, $options);
        } catch (\Exception $e) {
            $this->logger->critical(
                sprintf('Unhandled error save result: %s', $e->getMessage()),
                ['exception' => $e]
            );
            return self::REJECT;
        }

        return self::ACK;
    }

    /**
     * @param array $parameters
     *
     * @return array
     */
    private function configureOption($parameters = []): array
    {
        $optionResolver = new OptionsResolver();
        $optionResolver->setRequired('jobId');
        $optionResolver->setRequired('entity')->setAllowedTypes('entity', 'string');
        $optionResolver->setRequired('type')->setAllowedValues('type', [
            ProcessorRegistry::TYPE_EXPORT
        ]);
        $optionResolver->setDefined('userId')->setDefault('userId', null);
        $optionResolver->setDefined('owner')->setDefault('owner', null);

        $optionResolver->setDefined('options')
            ->setAllowedTypes('options', ['array'])
            ->setDefault('options', []);

        return $optionResolver->resolve($parameters);
    }

    /**
     * @param Job $job
     * @param array $parameters
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function saveJobResult(Job $job, array $parameters): void
    {
        $jobData = $job->getData();
        $this->importExportResultManager->saveResult(
            $job->getId(),
            $parameters['type'],
            $parameters['entity'],
            $parameters['owner'],
            $jobData['file'] ?? null,
            $parameters['options']
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics(): array
    {
        return [Topics::SAVE_IMPORT_EXPORT_RESULT];
    }
}
