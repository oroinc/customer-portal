<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Configuration;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfigurationInterface;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfigurationProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ImportExport configuration for CustomerUser addresses.
 */
class CustomerUserAddressImportExportConfigurationProvider implements ImportExportConfigurationProviderInterface
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function get(): ImportExportConfigurationInterface
    {
        return new ImportExportConfiguration(
            [
                ImportExportConfiguration::FIELD_ENTITY_CLASS => CustomerUserAddress::class,
                ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS =>
                    'oro_customer_customer_user_address',
                ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_customer_customer_user_address',
                ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'oro_customer_customer_user_address',
                ImportExportConfiguration::FIELD_EXPORT_BUTTON_LABEL =>
                    $this->translator->trans('oro.customer.customeruseraddress.export.button.label'),
                ImportExportConfiguration::FIELD_IMPORT_ENTITY_LABEL =>
                    $this->translator->trans('oro.customer.customeruseraddress.import.entity.label'),
                ImportExportConfiguration::FIELD_IMPORT_JOB_NAME => 'oro_customer_addresses_entity_import_from_csv'
            ]
        );
    }
}
