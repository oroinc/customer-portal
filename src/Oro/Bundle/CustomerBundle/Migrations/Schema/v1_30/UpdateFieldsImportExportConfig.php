<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_30;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Update ImportExport field configs for Customer, CustomerAddress and CustomerUserAddress entities.
 */
class UpdateFieldsImportExportConfig implements Migration, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $extendOptionsManager = $this->container->get('oro_entity_extend.migration.options_manager');

        $this->updateCustomerConfiguration($extendOptionsManager, $queries);
        $this->updateCustomerUserConfiguration($extendOptionsManager, $queries);
        $this->updateCustomerAddressConfiguration($extendOptionsManager, $queries);
        $this->updateCustomerUserAddressConfiguration($extendOptionsManager, $queries);
    }

    /**
     * Set name field as identity, make addresses excluded.
     */
    private function updateCustomerConfiguration(
        ExtendOptionsManager $extendOptionsManager,
        QueryBag $queries
    ): void {
        $extendOptionsManager->mergeColumnOptions(
            'oro_customer',
            'id',
            ['importexport' => ['identity' => -1]]
        );
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(Customer::class, 'id', 'importexport', 'identity', -1)
        );

        $extendOptionsManager->mergeColumnOptions(
            'oro_customer',
            'name',
            ['importexport' => ['identity' => -1]]
        );
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(Customer::class, 'name', 'importexport', 'identity', -1)
        );

        $extendOptionsManager->mergeColumnOptions(
            'oro_customer',
            'addresses',
            ['importexport' => ['excluded' => true]]
        );
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(Customer::class, 'addresses', 'importexport', 'excluded', true)
        );
    }

    /**
     * Set name field as identity, make addresses excluded.
     */
    private function updateCustomerUserConfiguration(
        ExtendOptionsManager $extendOptionsManager,
        QueryBag $queries
    ): void {
        $extendOptionsManager->mergeColumnOptions(
            'oro_customer_user',
            'addresses',
            ['importexport' => ['excluded' => true]]
        );
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(CustomerUser::class, 'addresses', 'importexport', 'excluded', true)
        );
    }

    /**
     * Update fields configuration for customer address
     *  - set id field as not excluded, identity, with header "Address ID"
     *  - add frontendOwner as identity field to prevent address stealing, not excluded, with header "Customer"
     *  - make primary field not excluded
     */
    private function updateCustomerAddressConfiguration(
        ExtendOptionsManager $extendOptionsManager,
        QueryBag $queries
    ): void {
        $extendOptionsManager->mergeColumnOptions(
            'oro_customer_address',
            'id',
            ['importexport' => ['header' => 'Address ID', 'excluded' => false]]
        );
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(CustomerAddress::class, 'id', 'importexport', 'header', 'Address ID')
        );
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(CustomerAddress::class, 'id', 'importexport', 'excluded', false)
        );

        $extendOptionsManager->mergeColumnOptions(
            'oro_customer_address',
            'frontendOwner',
            ['importexport' => ['identity' => true, 'header' => 'Customer', 'excluded' => false]]
        );
        $queries->addPostQuery(new UpdateEntityConfigFieldValueQuery(
            CustomerAddress::class,
            'frontendOwner',
            'importexport',
            'identity',
            true
        ));
        $queries->addPostQuery(new UpdateEntityConfigFieldValueQuery(
            CustomerAddress::class,
            'frontendOwner',
            'importexport',
            'header',
            'Customer'
        ));
        $queries->addPostQuery(new UpdateEntityConfigFieldValueQuery(
            CustomerAddress::class,
            'frontendOwner',
            'importexport',
            'excluded',
            false
        ));

        $extendOptionsManager->mergeColumnOptions(
            'oro_customer_address',
            'primary',
            ['importexport' => ['excluded' => false]]
        );
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(CustomerAddress::class, 'primary', 'importexport', 'excluded', false)
        );
    }

    /**
     * Update fields configuration for customer address
     *  - set id field as not excluded, identity, with header "Address ID"
     *  - add frontendOwner as identity field to prevent address stealing, not excluded, with header "Customer User"
     *  - make primary field not excluded
     */
    private function updateCustomerUserAddressConfiguration(
        ExtendOptionsManager $extendOptionsManager,
        QueryBag $queries
    ): void {
        $extendOptionsManager->mergeColumnOptions(
            'oro_customer_user_address',
            'id',
            ['importexport' => ['header' => 'Address ID', 'excluded' => false]]
        );
        $queries->addPostQuery(new UpdateEntityConfigFieldValueQuery(
            CustomerUserAddress::class,
            'id',
            'importexport',
            'header',
            'Address ID'
        ));
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(CustomerUserAddress::class, 'id', 'importexport', 'excluded', false)
        );

        $extendOptionsManager->mergeColumnOptions(
            'oro_customer_user_address',
            'frontendOwner',
            ['importexport' => ['identity' => true, 'header' => 'Customer User', 'excluded' => false]]
        );
        $queries->addPostQuery(new UpdateEntityConfigFieldValueQuery(
            CustomerUserAddress::class,
            'frontendOwner',
            'importexport',
            'identity',
            true
        ));
        $queries->addPostQuery(new UpdateEntityConfigFieldValueQuery(
            CustomerUserAddress::class,
            'frontendOwner',
            'importexport',
            'header',
            'Customer User'
        ));
        $queries->addPostQuery(new UpdateEntityConfigFieldValueQuery(
            CustomerUserAddress::class,
            'frontendOwner',
            'importexport',
            'excluded',
            false
        ));

        $extendOptionsManager->mergeColumnOptions(
            'oro_customer_user_address',
            'primary',
            ['importexport' => ['excluded' => false]]
        );
        $queries->addPostQuery(new UpdateEntityConfigFieldValueQuery(
            CustomerUserAddress::class,
            'primary',
            'importexport',
            'excluded',
            false
        ));
    }
}
