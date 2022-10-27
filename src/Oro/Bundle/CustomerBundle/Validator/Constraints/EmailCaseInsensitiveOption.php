<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Disallows to enable case-insensitive email addresses if there are duplicates in emailLowercase column.
 */
class EmailCaseInsensitiveOption extends Constraint
{
    public string $message = 'oro.customer.message.system_configuration.case_insensitive.duplicated_emails.message';

    public string $clickHere =
        'oro.customer.message.system_configuration.case_insensitive.duplicated_emails.click_here';
}
