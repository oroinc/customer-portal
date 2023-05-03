<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FormBundle\Event\FormHandler\AfterFormProcessEvent;
use Oro\Bundle\FormBundle\Event\FormHandler\Events;
use Oro\Bundle\FormBundle\Form\Handler\FormHandler;
use Oro\Bundle\WebsiteBundle\Provider\RequestWebsiteProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Registers and updates customer user.
 */
class FrontendCustomerUserHandler extends FormHandler
{
    /** @var RequestWebsiteProvider */
    private $requestWebsiteProvider;

    /** @var CustomerUserManager */
    private $userManager;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        DoctrineHelper $doctrineHelper,
        RequestWebsiteProvider $requestWebsiteProvider,
        CustomerUserManager $userManager
    ) {
        parent::__construct($eventDispatcher, $doctrineHelper);

        $this->requestWebsiteProvider = $requestWebsiteProvider;
        $this->userManager = $userManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process($data, FormInterface $form, Request $request)
    {
        $customerUser = $data;

        if (!$customerUser instanceof CustomerUser) {
            throw new \InvalidArgumentException(sprintf(
                'Data should be instance of %s, but %s is given',
                CustomerUser::class,
                is_object($customerUser) ? get_class($customerUser) : gettype($customerUser)
            ));
        }

        $isUpdated = parent::process($customerUser, $form, $request);

        // Reloads the user to reset its username. This is needed when the
        // username or password have been changed to avoid issues with the
        // security layer.
        if ($customerUser->getId()) {
            $this->userManager->reloadUser($customerUser);
        }

        return $isUpdated;
    }

    /**
     * {@inheritdoc}
     */
    protected function saveData($data, FormInterface $form)
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $data;

        $this->eventDispatcher->dispatch(new AfterFormProcessEvent($form, $customerUser), Events::BEFORE_FLUSH);

        if (!$customerUser->getId()) {
            $website = $this->requestWebsiteProvider->getWebsite();
            if (null !== $website) {
                $customerUser->setWebsite($website);
            }

            $this->userManager->register($customerUser);
        }

        if (null === $customerUser->getAuthStatus()) {
            $this->userManager->setAuthStatus($customerUser, CustomerUserManager::STATUS_ACTIVE);
        }
        $this->userManager->updateUser($customerUser);

        $this->eventDispatcher->dispatch(new AfterFormProcessEvent($form, $customerUser), Events::AFTER_FLUSH);
    }
}
