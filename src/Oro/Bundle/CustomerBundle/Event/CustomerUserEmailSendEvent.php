<?php

namespace Oro\Bundle\CustomerBundle\Event;

use Oro\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Represents email send event
 */
class CustomerUserEmailSendEvent extends Event
{
    public const NAME = 'oro_customer.customer_user_email_send_event';

    public function __construct(
        private UserInterface $customerUser,
        private string $emailTemplate,
        private array $emailTemplateParams = [],
        private ?object $scope = null
    ) {
    }

    public function getCustomerUser(): UserInterface
    {
        return $this->customerUser;
    }

    public function getEmailTemplate(): string
    {
        return $this->emailTemplate;
    }

    public function getEmailTemplateParams(): array
    {
        return $this->emailTemplateParams;
    }

    public function getScope(): ?object
    {
        return $this->scope;
    }

    public function setScope(?object $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function setEmailTemplate($emailTemplate): self
    {
        $this->emailTemplate = $emailTemplate;

        return $this;
    }

    public function setEmailTemplateParams(array $emailTemplateParams): self
    {
        $this->emailTemplateParams = $emailTemplateParams;

        return $this;
    }
}
