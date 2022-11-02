<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_26;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\EntityConfigBundle\Migration\RenameEntityConfigFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Renames field name in the entity config of User entity: roles to userRoles
 */
class RenameRolesField implements Migration, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        if (!$this->container->get(ApplicationState::class)->isInstalled()) {
            return;
        }

        $queries->addQuery(new RenameEntityConfigFieldQuery(CustomerUser::class, 'roles', 'userRoles'));
    }
}
