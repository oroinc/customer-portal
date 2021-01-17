<?php

namespace Oro\Bundle\CustomerBundle\Event;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Represents email send event
 */
class CustomerUserEmailSendEvent extends Event
{
    const NAME = 'oro_customer.customer_user_email_send_event';

    /**
     * @var CustomerUser
     */
    private $customerUser;

    /**
     * @var string
     */
    private $emailTemplate;

    /**
     * @var array
     */
    private $emailTemplateParams;

    /**
     * @param UserInterface $customerUser
     * @param string $emailTemplate
     * @param array $emailTemplateParams
     */
    public function __construct(UserInterface $customerUser, $emailTemplate, array $emailTemplateParams = [])
    {
        $this->customerUser = $customerUser;
        $this->emailTemplate = $emailTemplate;
        $this->emailTemplateParams = $emailTemplateParams;
    }

    /**
     * @return UserInterface
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }

    /**
     * @return string
     */
    public function getEmailTemplate()
    {
        return $this->emailTemplate;
    }

    /**
     * @return array
     */
    public function getEmailTemplateParams()
    {
        return $this->emailTemplateParams;
    }

    /**
     * @param $emailTemplate
     * @return $this
     */
    public function setEmailTemplate($emailTemplate)
    {
        $this->emailTemplate = $emailTemplate;

        return $this;
    }

    /**
     * @param array $emailTemplateParams
     * @return $this
     */
    public function setEmailTemplateParams(array $emailTemplateParams)
    {
        $this->emailTemplateParams = $emailTemplateParams;

        return $this;
    }
}
