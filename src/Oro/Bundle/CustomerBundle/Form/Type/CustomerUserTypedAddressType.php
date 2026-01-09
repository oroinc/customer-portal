<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

/**
 * Provides a form type for selecting default address types for customer users.
 *
 * This form type extends CustomerTypedAddressType to provide address type selection
 * functionality specifically for customer user entities, inheriting all the base
 * functionality for managing default address types.
 */
class CustomerUserTypedAddressType extends CustomerTypedAddressType
{
    public const NAME = 'oro_customer_customer_user_typed_address';
}
