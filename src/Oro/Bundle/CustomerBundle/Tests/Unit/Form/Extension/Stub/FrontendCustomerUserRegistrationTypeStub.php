<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension\Stub;

use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRegistrationType;
use Symfony\Component\Form\AbstractType;

class FrontendCustomerUserRegistrationTypeStub extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return FrontendCustomerUserRegistrationType::NAME;
    }
}
