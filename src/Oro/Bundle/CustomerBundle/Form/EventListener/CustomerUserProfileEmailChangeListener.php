<?php

namespace Oro\Bundle\CustomerBundle\Form\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Handler\ChangeCustomerUserEmailHandler;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\FormBundle\Event\FormHandler\AfterFormProcessEvent;

/**
 * Initializes customer user email change process
 */
class CustomerUserProfileEmailChangeListener
{
    private array $updatedUsers = [];

    public function __construct(
        private FeatureChecker $featureChecker,
        private ChangeCustomerUserEmailHandler $changeCustomerUserEmailHandler,
        private CustomerUserManager $customerUserManager,
    ) {
    }

    public function afterFlush(AfterFormProcessEvent $event): void
    {
        if (!$this->featureChecker->isFeatureEnabled('customer_user_email_change_verification_enabled')) {
            return;
        }

        $customerUser = $event->getData();

        if ($customerUser->getNewEmail() === null) {
            return;
        }

        $this->changeCustomerUserEmailHandler->initializeEmailChangeAndSendToOldEmail($customerUser);
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        if (!$this->featureChecker->isFeatureEnabled('customer_user_email_change_verification_enabled')) {
            return;
        }

        $em = $eventArgs->getObjectManager();
        $uow = $em->getUnitOfWork();
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (
                $entity instanceof CustomerUser
                && \array_key_exists('email', $uow->getEntityChangeSet($entity))
                && $entity->getNewEmail() !== null
            ) {
                [$oldEmail, ] = $uow->getEntityChangeSet($entity)['email'];
                $this->updatedUsers[$oldEmail] = $entity;
            }
        }
    }

    public function postFlush(PostFlushEventArgs $event): void
    {
        try {
            foreach ($this->updatedUsers as $oldEmail => $customerUser) {
                $customerUser->setNewEmail(null);
                $customerUser->setEmailVerificationCodeRequestedAt(null);
                $customerUser->setNewEmailVerificationCode(null);
                unset($this->updatedUsers[$oldEmail]);

                $this->customerUserManager->updateUser($customerUser);
                $this->changeCustomerUserEmailHandler->sendFinishEmail($customerUser, $oldEmail);
            }
        } finally {
            $this->updatedUsers = [];
        }
    }
}
