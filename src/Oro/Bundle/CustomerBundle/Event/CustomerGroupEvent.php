<?php

namespace Oro\Bundle\CustomerBundle\Event;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\FormBundle\Event\FormHandler\AfterFormProcessEvent;
use Symfony\Component\Form\FormInterface;

/**
 * Event dispatched during customer group form processing and lifecycle.
 *
 * This event is triggered before customer group removal and before flushing changes to the database,
 * allowing listeners to perform validation, cleanup, or related operations on customer groups.
 */
class CustomerGroupEvent extends AfterFormProcessEvent
{
    public const PRE_REMOVE = 'oro_customer.customer_group.pre_remove';
    public const BEFORE_FLUSH = 'oro_customer.customer_group.before_flush';

    public function __construct(CustomerGroup $customerGroup, ?FormInterface $form = null)
    {
        $this->data = $customerGroup;
        $this->form = $form;
    }

    /**
     * @return CustomerGroup
     */
    #[\Override]
    public function getData()
    {
        return $this->data;
    }
}
