<?php

namespace Oro\Bundle\FrontendBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ConfigBundle\Migration\RenameConfigSectionQuery;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class OroFrontendBundleInstaller implements Installation, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        // Migration version was increased without creating correct migration
        // Use next version v1_2 in case of new migration and remove this comment
        return 'v1_1';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        // update system configuration for installed instances
        if ($this->container->get(ApplicationState::class)->isInstalled()) {
            $queries->addPostQuery(new RenameConfigSectionQuery('oro_b2b_frontend', 'oro_frontend'));
        }
    }
}
