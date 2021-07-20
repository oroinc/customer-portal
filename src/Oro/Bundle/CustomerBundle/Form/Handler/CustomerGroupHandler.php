<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Event\CustomerGroupEvent;
use Oro\Bundle\CustomerBundle\Event\CustomerMassEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Form handler for customer group
 * - process customer group form
 * - save changes to customer group entity
 * - assign/unassign customers to customer group
 * - trigger oro_customer.customer_group.before_flush and oro_customer.customer.on_customer_group_mass_change events
 */
class CustomerGroupHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var ObjectManager */
    protected $manager;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->form = $form;
        $this->request = $request;
        $this->manager = $manager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Process form
     *
     * @param CustomerGroup $entity
     * @return bool  True on successful processing, false otherwise
     */
    public function process(CustomerGroup $entity)
    {
        $this->form->setData($entity);

        if ($this->request->isMethod('POST')) {
            $this->form->handleRequest($this->request);

            if ($this->form->isSubmitted() && $this->form->isValid()) {
                $this->onSuccess(
                    $entity,
                    $this->form->get('appendCustomers')->getData(),
                    $this->form->get('removeCustomers')->getData()
                );

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param CustomerGroup $entity
     * @param Customer[] $append
     * @param Customer[] $remove
     */
    protected function onSuccess(CustomerGroup $entity, array $append, array $remove)
    {
        $this->setGroup($entity, $append);
        $this->removeFromGroup($entity, $remove);

        $event = new CustomerGroupEvent($entity, $this->form);
        $this->dispatcher->dispatch($event, CustomerGroupEvent::BEFORE_FLUSH);

        $this->manager->persist($entity);
        $this->manager->flush();

        $changedCustomers = array_merge($append, $remove);
        if ($changedCustomers) {
            $customerMassEvent = new CustomerMassEvent($changedCustomers);
            $this->dispatcher->dispatch($customerMassEvent, CustomerMassEvent::ON_CUSTOMER_GROUP_MASS_CHANGE);
        }
    }

    /**
     * Append customers to customer group
     *
     * @param CustomerGroup $group
     * @param Customer[] $customers
     */
    protected function setGroup(CustomerGroup $group, array $customers)
    {
        foreach ($customers as $customer) {
            $customer->setGroup($group);
            $this->manager->persist($customer);
        }
    }

    /**
     * Remove users from business unit
     *
     * @param CustomerGroup $group
     * @param Customer[] $customers
     */
    protected function removeFromGroup(CustomerGroup $group, array $customers)
    {
        foreach ($customers as $customer) {
            if ($customer->getGroup()->getId() === $group->getId()) {
                $customer->setGroup(null);
                $this->manager->persist($customer);
            }
        }
    }
}
