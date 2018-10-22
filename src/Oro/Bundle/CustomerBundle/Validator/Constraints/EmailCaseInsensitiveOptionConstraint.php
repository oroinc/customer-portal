<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class EmailCaseInsensitiveOptionConstraint extends Constraint
{
    /** @var string */
    public $message = 'oro.customer.message.system_configuration.case_insensitive.duplicated_emails.message';

    /** @var string */
    public $clickHere = 'oro.customer.message.system_configuration.case_insensitive.duplicated_emails.click_here';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'oro_customer.validator.email_case_insensitive_option';
    }
}
