<?php

namespace Oro\Bundle\CustomerBundle\Model;

use Oro\Bundle\UserBundle\Entity\AbstractRole;

abstract class ExtendCustomerUserRole extends AbstractRole
{
    /**
     * Constructor
     *
     * The real implementation of this method is auto generated.
     *
     * IMPORTANT: If the derived class has own constructor it must call parent constructor.
     */
    public function __construct(string $role = '')
    {
        parent::__construct($role);
    }
}
