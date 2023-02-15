<?php

namespace Oro\Bundle\CustomerBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Form\FormUtil;
use Oro\Bundle\ApiBundle\Processor\CustomizeFormData\CustomizeFormDataContext;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Prepares a new CustomerUser entity to be saved to the database.
 */
class UpdateNewCustomerUser implements ProcessorInterface
{
    private CustomerUserManager $userManager;

    public function __construct(CustomerUserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context): void
    {
        /** @var CustomizeFormDataContext $context */

        $form = $context->getForm();
        if (!FormUtil::isSubmittedAndValid($form)) {
            return;
        }

        /** @var CustomerUser $user */
        $user = $form->getData();

        // generate random secure password for a user
        if (!$user->getPlainPassword()) {
            $user->setPlainPassword($this->userManager->generatePassword());
        }

        $this->userManager->updateUser($user, false);
    }
}
