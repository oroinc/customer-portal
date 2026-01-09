<?php

namespace Oro\Bundle\WebsiteBundle\Entity;

/**
 * Defines the contract for entities that are aware of both website and currency context.
 *
 * Entities implementing this interface can be associated with specific websites and currencies,
 * allowing for multi-website and multi-currency support. This interface extends {@see WebsiteAwareInterface}
 * to inherit website awareness while adding implicit currency awareness capabilities.
 */
interface WebsiteBasedCurrencyAwareInterface extends WebsiteAwareInterface
{
}
