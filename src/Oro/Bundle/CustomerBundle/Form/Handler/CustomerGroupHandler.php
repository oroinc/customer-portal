<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Event\CustomerGroupEvent;
use Oro\Bundle\CustomerBundle\Event\CustomerMassEvent;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
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
class CustomerGroupHandler implements FormHandlerInterface
{
    protected ObjectManager $manager;
    protected EventDispatcherInterface $dispatcher;

    public function __construct(ObjectManager $manager, EventDispatcherInterface $dispatcher)
    {
        $this->manager = $manager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function process($entity, FormInterface $form, Request $request)
    {
        $form->setData($entity);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->onSuccess(
                    $entity,
                    $form,
                    $form->get('appendCustomers')->getData(),
                    $form->get('removeCustomers')->getData()
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
     * @param FormInterface $form
     * @param Customer[] $append
     * @param Customer[] $remove
     */
    protected function onSuccess(CustomerGroup $entity, FormInterface $form, array $append, array $remove): void
    {
        $this->setGroup($entity, $append);
        $this->removeFromGroup($entity, $remove);
        $event = new CustomerGroupEvent($entity, $form);
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
    protected function setGroup(CustomerGroup $group, array $customers): void
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
    protected function removeFromGroup(CustomerGroup $group, array $customers): void
    {
        foreach ($customers as $customer) {
            if ($customer->getGroup()->getId() === $group->getId()) {
                $customer->setGroup(null);
                $this->manager->persist($customer);
            }
        }
    }
}
