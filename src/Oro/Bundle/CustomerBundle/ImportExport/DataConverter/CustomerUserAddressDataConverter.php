<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\DataConverter;

/**
 * Import-Export Data converter for Customer User Address
 *  - adds Customer User ID
 *  - renames Customer User Email Address to Email Address
 */
class CustomerUserAddressDataConverter extends AddressDataConverter
{
    protected function getHeaderConversionRules()
    {
        $rules = parent::getHeaderConversionRules();

        if (array_key_exists('Customer User Email Address', $rules)) {
            $rules['Email Address'] = $rules['Customer User Email Address'];
            unset($rules['Customer User Email Address']);
        }
        $rules['Customer User ID'] = 'frontendOwner:id';

        return $rules;
    }

    protected function getBackendHeader()
    {
        return array_merge(
            parent::getBackendHeader(),
            [
                'frontendOwner:id'
            ]
        );
    }
}
