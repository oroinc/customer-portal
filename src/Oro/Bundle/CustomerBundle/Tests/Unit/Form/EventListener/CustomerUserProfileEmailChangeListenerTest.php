<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\EventListener\CustomerUserProfileEmailChangeListener;
use Oro\Bundle\CustomerBundle\Handler\ChangeCustomerUserEmailHandler;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\FormBundle\Event\FormHandler\AfterFormProcessEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerUserProfileEmailChangeListenerTest extends TestCase
{
    private FeatureChecker&MockObject $featureChecker;
    private ChangeCustomerUserEmailHandler&MockObject $handler;
    private CustomerUserManager&MockObject $customerUserManager;
    private CustomerUserProfileEmailChangeListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->featureChecker = $this->createMock(FeatureChecker::class);
        $this->handler = $this->createMock(ChangeCustomerUserEmailHandler::class);
        $this->customerUserManager = $this->createMock(CustomerUserManager::class);

        $this->listener = new CustomerUserProfileEmailChangeListener(
            $this->featureChecker,
            $this->handler,
            $this->customerUserManager
        );
    }

    public function testAfterFlushWhenFeatureDisabled(): void
    {
        $event = $this->createMock(AfterFormProcessEvent::class);

        $this->featureChecker->expects(self::once())
            ->method('isFeatureEnabled')
            ->with('customer_user_email_change_verification_enabled')
            ->willReturn(false);
        $this->handler->expects(self::never())
            ->method('initializeEmailChangeAndSendToOldEmail');

        $this->listener->afterFlush($event);
    }

    public function testAfterFlushWhenFeatureEnabled(): void
    {
        $customerUser = new CustomerUser();
        $customerUser->setNewEmail('new@example.com');
        $event = $this->createMock(AfterFormProcessEvent::class);

        $this->featureChecker->expects(self::once())
            ->method('isFeatureEnabled')
            ->with('customer_user_email_change_verification_enabled')
            ->willReturn(true);
        $event->expects(self::once())
            ->method('getData')
            ->willReturn($customerUser);
        $this->handler->expects(self::once())
            ->method('initializeEmailChangeAndSendToOldEmail')
            ->with($customerUser);

        $this->listener->afterFlush($event);
    }

    public function testAfterFlushWhenNewEmailIsNull(): void
    {
        $customerUser = new CustomerUser();
        $event = $this->createMock(AfterFormProcessEvent::class);

        $this->featureChecker->expects(self::once())
            ->method('isFeatureEnabled')
            ->with('customer_user_email_change_verification_enabled')
            ->willReturn(true);
        $event->expects(self::once())
            ->method('getData')
            ->willReturn($customerUser);
        $this->handler->expects(self::never())
            ->method('initializeEmailChangeAndSendToOldEmail');

        $this->listener->afterFlush($event);
    }

    public function testPostFlush(): void
    {
        $customerUser = new CustomerUser();
        $customerUser->setEmail('new@example.com');
        $customerUser->setNewEmail('new@example.com');
        $customerUser->setNewEmailVerificationCode('code');
        $customerUser->setEmailVerificationCodeRequestedAt(new \DateTimeImmutable('2024-01-01T00:00:00Z'));
        $oldEmail = 'old@example.com';

        $this->featureChecker->expects(self::once())
            ->method('isFeatureEnabled')
            ->with('customer_user_email_change_verification_enabled')
            ->willReturn(true);

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects(self::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([$customerUser]);
        $unitOfWork->expects(self::exactly(2))
            ->method('getEntityChangeSet')
            ->with($customerUser)
            ->willReturn(['email' => [$oldEmail, 'new@example.com']]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($unitOfWork);

        $this->customerUserManager->expects(self::once())
            ->method('updateUser')
            ->with($customerUser);
        $this->handler->expects(self::once())
            ->method('sendFinishEmail')
            ->with($customerUser, $oldEmail);

        $this->listener->onFlush(new OnFlushEventArgs($entityManager));
        $this->listener->postFlush($this->createMock(PostFlushEventArgs::class));

        self::assertNull($customerUser->getNewEmail());
        self::assertNull($customerUser->getNewEmailVerificationCode());
        self::assertNull($customerUser->getEmailVerificationCodeRequestedAt());
    }
}
