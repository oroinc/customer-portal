<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_14;

use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCustomerBundle implements Migration, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateConfig();
    }

    public function updateConfig()
    {
        $dumper = $this->container->get('oro_entity_extend.tools.dumper');
        $dumper->updateConfig(function (ConfigInterface $config) {
            $configId  = $config->getId();
            $className = $configId->getClassName();

            return $className === CustomerUser::class || $className === CustomerUserRole::class;
        });
    }
}
