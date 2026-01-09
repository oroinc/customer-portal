<?php

namespace Oro\Bundle\FrontendBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ConfigBundle\Migration\RenameConfigSectionQuery;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Component\DependencyInjection\ContainerAwareInterface;
use Oro\Component\DependencyInjection\ContainerAwareTrait;

class OroFrontendBundleInstaller implements Installation, ContainerAwareInterface
{
    use ContainerAwareTrait;

    #[\Override]
    public function getMigrationVersion(): string
    {
        // Migration version was increased without creating correct migration
        // Use next version v1_2 in case of new migration and remove this comment
        return 'v1_1';
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        // update system configuration for installed instances
        if ($this->container->get(ApplicationState::class)->isInstalled()) {
            $queries->addPostQuery(new RenameConfigSectionQuery('oro_b2b_frontend', 'oro_frontend'));
        }
    }
}
