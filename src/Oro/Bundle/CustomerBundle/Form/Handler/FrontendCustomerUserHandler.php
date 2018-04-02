<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FormBundle\Event\FormHandler\AfterFormProcessEvent;
use Oro\Bundle\FormBundle\Event\FormHandler\Events;
use Oro\Bundle\FormBundle\Form\Handler\FormHandler;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FrontendCustomerUserHandler extends FormHandler
{
    /** @var RequestStack */
    private $requestStack;

    /** @var CustomerUserManager */
    private $userManager;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param DoctrineHelper $doctrineHelper
     * @param RequestStack $requestStack
     * @param CustomerUserManager $userManager
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        DoctrineHelper $doctrineHelper,
        RequestStack $requestStack,
        CustomerUserManager $userManager
    ) {
        parent::__construct($eventDispatcher, $doctrineHelper);

        $this->requestStack = $requestStack;
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
                gettype($customerUser) === 'object' ? get_class($customerUser) : gettype($customerUser)
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

        $this->eventDispatcher->dispatch(Events::BEFORE_FLUSH, new AfterFormProcessEvent($form, $customerUser));

        if (!$customerUser->getId()) {
            $request = $this->requestStack->getMasterRequest();
            $website = $request->attributes->get('current_website');
            if ($website instanceof Website) {
                $customerUser->setWebsite($website);
            }

            $this->userManager->register($customerUser);
        }

        $this->userManager->updateUser($customerUser);

        $this->eventDispatcher->dispatch(Events::AFTER_FLUSH, new AfterFormProcessEvent($form, $customerUser));
    }
}
