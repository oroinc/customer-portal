<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\DataConverter;

use Oro\Bundle\ImportExportBundle\Converter\ConfigurableTableDataConverter;

/**
 * Import-Export Data converter for Typed Address entities (Customer User Address and Customer Address)
 *  - adds human friendly types
 *  - adds Delete header
 */
class AddressDataConverter extends ConfigurableTableDataConverter
{
    protected function getBackendHeader()
    {
        return array_merge(
            parent::getBackendHeader(),
            [
                'Billing',
                'Default Billing',
                'Shipping',
                'Default Shipping',
                'Delete'
            ]
        );
    }
}
