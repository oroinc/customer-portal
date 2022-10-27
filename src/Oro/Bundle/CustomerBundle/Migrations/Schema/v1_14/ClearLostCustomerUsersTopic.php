<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_14;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic to delete customer users without assigned customer. Should be used only during the schema upgrade v1_14.
 */
class ClearLostCustomerUsersTopic extends AbstractTopic
{
    public const BATCH_NUMBER = 'batch_number';

    public static function getName(): string
    {
        return 'oro_customer.clear_lost_customer_users';
    }

    public static function getDescription(): string
    {
        return 'Delete customer users without assigned customer. Should be used only during the schema upgrade v1_14.';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->define(self::BATCH_NUMBER)
            ->allowedTypes('int');
    }
}
