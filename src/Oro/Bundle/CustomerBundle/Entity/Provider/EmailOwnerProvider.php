<?php

namespace Oro\Bundle\CustomerBundle\Entity\Provider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface;

/**
 * Email owner provider for Customer User
 */
class EmailOwnerProvider implements EmailOwnerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEmailOwnerClass()
    {
        return CustomerUser::class;
    }

    /**
     * {@inheritdoc}
     */
    public function findEmailOwner(EntityManager $em, $email)
    {
        return $em->getRepository(CustomerUser::class)->findOneBy(['email' => $email]);
    }
}
