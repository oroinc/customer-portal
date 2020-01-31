<?php

namespace Oro\Bundle\CustomerBundle\Api\Processor\Create;

use Oro\Bundle\ApiBundle\Processor\Create\CreateContext;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Saves new CustomerUser entity to the database.
 * @deprecated replaced with Oro\Bundle\CustomerBundle\Api\Processor\UpdateNewCustomerUser
 */
class SaveCustomerUser implements ProcessorInterface
{
    /**
     * @var CustomerUserManager
     */
    protected $userManager;

    /**
     * @param CustomerUserManager $userManager
     */
    public function __construct(CustomerUserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
    }
}
