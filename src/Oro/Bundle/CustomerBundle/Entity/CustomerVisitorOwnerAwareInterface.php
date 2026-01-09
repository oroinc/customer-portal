<?php

namespace Oro\Bundle\CustomerBundle\Entity;

/**
 * Defines the contract for entities that are owned by a customer visitor.
 *
 * Implementing classes represent entities that have ownership relationships with customer visitors,
 * allowing the system to track and manage entity ownership for anonymous or guest users.
 */
interface CustomerVisitorOwnerAwareInterface
{
    /**
     * @return \Oro\Bundle\CustomerBundle\Entity\CustomerVisitor
     */
    public function getVisitor();
}
