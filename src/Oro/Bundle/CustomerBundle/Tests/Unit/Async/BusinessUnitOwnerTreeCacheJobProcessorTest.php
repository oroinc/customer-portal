<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Async;

use Oro\Bundle\CustomerBundle\Async\BusinessUnitOwnerTreeCacheJobProcessor;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Model\BusinessUnitMessageFactory;
use Oro\Bundle\CustomerBundle\Owner\FrontendOwnerTreeProvider;
use Oro\Bundle\TestFrameworkBundle\Test\Logger\LoggerAwareTraitTestTrait;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Test\JobRunner;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;

class BusinessUnitOwnerTreeCacheJobProcessorTest extends \PHPUnit\Framework\TestCase
{
    use LoggerAwareTraitTestTrait;

    private const JOB_ID = 5;

    private BusinessUnitMessageFactory|\PHPUnit\Framework\MockObject\MockObject $messageFactory;

    private FrontendOwnerTreeProvider|\PHPUnit\Framework\MockObject\MockObject $frontendOwnerTreeProvider;

    private BusinessUnitOwnerTreeCacheJobProcessor $processor;

    private JobRunner $jobRunner;

    protected function setUp(): void
    {
        $this->messageFactory = $this->createMock(BusinessUnitMessageFactory::class);
        $this->frontendOwnerTreeProvider = $this->createMock(FrontendOwnerTreeProvider::class);
        $this->jobRunner = new JobRunner();

        $this->processor = new BusinessUnitOwnerTreeCacheJobProcessor(
            $this->jobRunner,
            $this->frontendOwnerTreeProvider,
            $this->messageFactory
        );
        $this->setUpLoggerMock($this->processor);
    }

    public function testProcess(): void
    {
        $this->messageFactory->expects(self::once())
            ->method('getJobIdFromMessage')
            ->willReturn(self::JOB_ID);

        $businessUnit = new Customer();
        $this->messageFactory->expects(self::once())
            ->method('getBusinessUnitFromMessage')
            ->willReturn($businessUnit);

        $this->frontendOwnerTreeProvider->expects(self::once())
            ->method('getTreeByBusinessUnit')
            ->with($businessUnit);

        $this->loggerMock->expects(self::never())
            ->method('error');

        $message = new Message();
        $message->setBody([]);

        $session = $this->createMock(SessionInterface::class);
        self::assertEquals(MessageProcessorInterface::ACK, $this->processor->process($message, $session));
    }

    public function testProcessWithDelayedJobResult(): void
    {
        $this->messageFactory->expects(self::once())
            ->method('getJobIdFromMessage')
            ->willReturn(self::JOB_ID);

        $businessUnit = new Customer();
        $this->messageFactory->expects(self::once())
            ->method('getBusinessUnitFromMessage')
            ->willReturn($businessUnit);

        $this->frontendOwnerTreeProvider->expects(self::exactly(2))
            ->method('getTreeByBusinessUnit')
            ->with($businessUnit);

        $message = new Message();
        $message->setBody([]);

        $session = $this->createMock(SessionInterface::class);
        self::assertEquals(MessageProcessorInterface::ACK, $this->processor->process($message, $session));

        $delayedJobs = $this->jobRunner->getRunDelayedJobs();
        self::assertCount(1, $delayedJobs);

        $delayedJob = reset($delayedJobs);
        self::assertTrue(call_user_func($delayedJob['runCallback']));
    }
}
