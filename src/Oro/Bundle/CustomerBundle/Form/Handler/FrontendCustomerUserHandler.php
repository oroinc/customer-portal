<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\CustomerBundle\Event\CustomerUserRegisterEvent;

class FrontendCustomerUserHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var CustomerUserManager */
    protected $userManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param CustomerUserManager $userManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        CustomerUserManager $userManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->form = $form;
        $this->request = $request;
        $this->userManager = $userManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Process form
     *
     * @param CustomerUser $customerUser
     * @return bool True on successful processing, false otherwise
     */
    public function process(CustomerUser $customerUser)
    {
        $isUpdated = false;
        $isRegistered = false;
        if (in_array($this->request->getMethod(), ['POST', 'PUT'], true)) {
            $this->form->submit($this->request);
            if ($this->form->isValid()) {
                if (!$customerUser->getId()) {
                    $website = $this->request->attributes->get('current_website');
                    if ($website instanceof Website) {
                        $customerUser->setWebsite($website);
                    }
                    $this->userManager->register($customerUser);
                    $isRegistered = true;
                }

                $this->userManager->updateUser($customerUser);

                if ($isRegistered) {
                    $event = new CustomerUserRegisterEvent($customerUser);
                    $this->eventDispatcher->dispatch(CustomerUserRegisterEvent::NAME, $event);
                }

                $isUpdated = true;
            }
        }
        // Reloads the user to reset its username. This is needed when the
        // username or password have been changed to avoid issues with the
        // security layer.
        if ($customerUser->getId()) {
            $this->userManager->reloadUser($customerUser);
        }

        return $isUpdated;
    }
}
