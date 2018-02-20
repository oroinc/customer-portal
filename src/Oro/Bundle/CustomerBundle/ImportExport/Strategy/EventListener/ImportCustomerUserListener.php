<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Strategy\EventListener;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\Translation\TranslatorInterface;

class ImportCustomerUserListener
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var CustomerUserManager
     */
    protected $customerUserManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ImportStrategyHelper
     */
    protected $strategyHelper;

    /**
     * @param ManagerRegistry $registry
     * @param CustomerUserManager $customerUserManager
     * @param TranslatorInterface $translator
     * @param ImportStrategyHelper $strategyHelper
     */
    public function __construct(
        ManagerRegistry $registry,
        CustomerUserManager $customerUserManager,
        TranslatorInterface $translator,
        ImportStrategyHelper $strategyHelper
    ) {
        $this->registry = $registry;
        $this->customerUserManager = $customerUserManager;
        $this->translator = $translator;
        $this->strategyHelper = $strategyHelper;
    }

    /**
     * @param StrategyEvent $event
     */
    public function onProcessAfter(StrategyEvent $event)
    {
        $entity = $event->getEntity();
        $context = $event->getContext();

        if (!$entity instanceof CustomerUser) {
            return;
        }

        $this->updatePasswordIfEmpty($entity);

        if (!$this->updateWebsiteWithDefaultIfEmpty($entity)) {
            $error = $this->translator->trans(
                'oro.customer.customeruser.import.message.default_website_does_not_exist'
            );
            $this->updateContextWithError($context, $error);
            $event->setEntity(null);
        }

        if (!$entity->getId() && !$this->updateRoleByWebsiteIfEmpty($entity)) {
            $error = $this->translator->trans(
                'oro.customer.customeruser.import.message.default_website_role_does_not_exist',
                ['%website%' => (string) $entity->getWebsite()]
            );
            $this->updateContextWithError($context, $error);
            $event->setEntity(null);
        }
    }

    /**
     * @param CustomerUser $customerUser
     * @return bool
     */
    protected function updateWebsiteWithDefaultIfEmpty(CustomerUser $customerUser)
    {
        if ($customerUser->getWebsite()) {
            return true;
        }

        if ($website = $this->registry->getRepository(Website::class)->getDefaultWebsite()) {
            $customerUser->setWebsite($website);
            return true;
        }

        return false;
    }

    /**
     * @param CustomerUser $customerUser
     * @return bool
     */
    protected function updateRoleByWebsiteIfEmpty(CustomerUser $customerUser)
    {
        if (count($customerUser->getRoles()) > 0) {
            return true;
        }

        $website = $customerUser->getWebsite();
        if (!$website) {
            return false;
        }

        $role = $this->registry
            ->getRepository(CustomerUserRole::class)
            ->getDefaultCustomerUserRoleByWebsite($website);

        if ($role) {
            $customerUser->addRole($role);
            return true;
        }

        return false;
    }

    /**
     * @param CustomerUser $customerUser
     */
    protected function updatePasswordIfEmpty(CustomerUser $customerUser)
    {
        if ($customerUser->getPassword()) {
            return;
        }

        $customerUser->setPlainPassword($this->customerUserManager->generatePassword(10));
        $this->customerUserManager->updatePassword($customerUser);
    }

    /**
     * @param $context
     * @param string $error
     */
    protected function updateContextWithError(ContextInterface $context, $error)
    {
        $context->incrementErrorEntriesCount();
        $this->strategyHelper->addValidationErrors([$error], $context);
    }
}
