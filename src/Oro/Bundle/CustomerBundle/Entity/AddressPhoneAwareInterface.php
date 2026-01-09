<?php

namespace Oro\Bundle\CustomerBundle\Entity;

/**
 * Defines the contract for address entities that include phone number information.
 *
 * Implementing classes represent addresses that can store and retrieve phone numbers,
 * enabling contact information management for customer and customer user addresses.
 */
interface AddressPhoneAwareInterface
{
    /**
     * Get phone number
     *
     * @return string
     */
    public function getPhone();

    /**
     * Set phone number
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone);
}
