<?php

namespace Oro\Bundle\CustomerBundle\Model;

use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\UserBundle\Entity\AbstractUser;

/**
 * This class is required to make CustomerUser entity extendable.
 *
 * @method setAuthStatus(AbstractEnumValue $enum)
 * @method AbstractEnumValue getAuthStatus()
 */
abstract class ExtendCustomerUser extends AbstractUser
{
    /**
     * Constructor
     *
     * The real implementation of this method is auto generated.
     *
     * IMPORTANT: If the derived class has own constructor it must call parent constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
}
