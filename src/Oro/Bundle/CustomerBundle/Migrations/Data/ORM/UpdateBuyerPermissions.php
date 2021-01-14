<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\FrontendBundle\Migrations\Data\ORM\LoadUserRolesData;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractUpdatePermissions;

/**
 * Updates permissions for CustomerUser entity for ROLE_FRONTEND_BUYER storefront role.
 */
class UpdateBuyerPermissions extends AbstractUpdatePermissions
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadUserRolesData::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $aclManager = $this->getAclManager();
        if (!$aclManager->isAclEnabled()) {
            return;
        }

        $this->replaceEntityPermissions(
            $aclManager,
            $this->getRole($manager, 'ROLE_FRONTEND_BUYER', CustomerUserRole::class),
            CustomerUser::class,
            ['VIEW_LOCAL']
        );
        $aclManager->flush();
    }
}
