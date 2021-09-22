<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\EmailBundle\Model\EmailRecipientsProviderArgs;
use Oro\Bundle\EmailBundle\Provider\EmailRecipientsHelper;
use Oro\Bundle\EmailBundle\Provider\EmailRecipientsProviderInterface;

/**
 * Provider for email recipient list based on customer users
 */
class EmailRecipientsProvider implements EmailRecipientsProviderInterface
{
    /** @var Registry */
    protected $registry;

    /** @var EmailRecipientsHelper */
    protected $emailRecipientsHelper;

    public function __construct(
        Registry $registry,
        EmailRecipientsHelper $emailRecipientsHelper
    ) {
        $this->registry = $registry;
        $this->emailRecipientsHelper = $emailRecipientsHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipients(EmailRecipientsProviderArgs $args)
    {
        return $this->emailRecipientsHelper->getRecipients(
            $args,
            $this->getCustomerUserRepository(),
            'cu',
            CustomerUser::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSection(): string
    {
        return 'oro.customer.customeruser.entity_plural_label';
    }

    /**
     * @return CustomerUserRepository
     */
    protected function getCustomerUserRepository()
    {
        return $this->registry->getRepository(CustomerUser::class);
    }
}
