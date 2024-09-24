<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueCustomerUserNameAndEmail;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FormBundle\Event\FormHandler\AfterFormProcessEvent;
use Oro\Bundle\FormBundle\Event\FormHandler\Events;
use Oro\Bundle\FormBundle\Form\Handler\FormHandler;
use Oro\Bundle\WebsiteBundle\Provider\RequestWebsiteProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Registers and updates customer user.
 */
class FrontendCustomerUserHandler extends FormHandler
{
    private RequestWebsiteProvider $requestWebsiteProvider;
    private CustomerUserManager $userManager;
    private ConfigManager $configManager;

    private bool $ignoreNotUniqueEmailValidationError = true;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        DoctrineHelper $doctrineHelper,
        RequestWebsiteProvider $requestWebsiteProvider,
        CustomerUserManager $userManager,
        ConfigManager $configManager
    ) {
        parent::__construct($eventDispatcher, $doctrineHelper);

        $this->requestWebsiteProvider = $requestWebsiteProvider;
        $this->userManager = $userManager;
        $this->configManager = $configManager;
    }

    public function setIgnoreNotUniqueEmailValidationError(bool $ignoreNotUniqueEmailValidationError = true): void
    {
        $this->ignoreNotUniqueEmailValidationError = $ignoreNotUniqueEmailValidationError;
    }

    #[\Override]
    public function process($data, FormInterface $form, Request $request)
    {
        $customerUser = $data;

        if (!$customerUser instanceof CustomerUser) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Data should be instance of %s, but %s is given',
                    CustomerUser::class,
                    is_object($customerUser) ? get_class($customerUser) : gettype($customerUser)
                )
            );
        }

        $isUpdated = parent::process($customerUser, $form, $request);

        // Reloads the user to reset its username. This is needed when the
        // username or password have been changed to avoid issues with the
        // security layer.
        if ($customerUser->getId()) {
            $this->userManager->reloadUser($customerUser);
        }


        return $this->processValidation($form, $customerUser, $isUpdated);
    }

    private function processValidation(FormInterface $form, CustomerUser $customerUser, bool $isUpdated): bool
    {
        if (!$this->isEmailValidationSupressed()) {
            return $isUpdated;
        }

        $formErrors = $form->getErrors(true);
        /** @var FormError[] $fieldErrors */
        $fieldErrors = $formErrors->findByCodes(UniqueCustomerUserNameAndEmail::NOT_UNIQUE_EMAIL);
        foreach ($fieldErrors as $fieldError) {
            // Clear non unique email validation errors.
            $this->removeFieldError($fieldError->getOrigin(), UniqueCustomerUserNameAndEmail::NOT_UNIQUE_EMAIL);

            // If the form does not contain any other errors, send a notification.
            if ($formErrors->count() === 1) {
                $user = $this->userManager->findUserByEmail($customerUser->getEmail());
                if ($user instanceof CustomerUser) {
                    $this->userManager->sendDuplicateEmailNotification($user);

                    // Emulate the behavior of a registered user.
                    return true;
                }
            }
        }

        return $isUpdated;
    }

    private function removeFieldError(?FormInterface $field, string $errorCode): void
    {
        if (!$field) {
            return;
        }

        /** @var FormError[] $fieldErrors */
        $fieldErrors = $field->getErrors(true);
        // Remove all errors and add back only non-filtered out ones.
        $field->clearErrors();
        foreach ($fieldErrors as $error) {
            $cause = $error->getCause();
            if ($cause instanceof ConstraintViolation && $cause->getCode() !== $errorCode) {
                $field->addError($error);
            }
        }
    }

    private function isEmailValidationSupressed(): bool
    {
        return $this->ignoreNotUniqueEmailValidationError
            && $this->configManager->get('oro_customer.email_enumeration_protection_enabled');
    }

    #[\Override]
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
