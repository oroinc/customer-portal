<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerType;
use Oro\Bundle\CustomerBundle\JsTree\CustomerTreeHandler;
use Oro\Bundle\FormBundle\Model\UpdateHandler;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Back-office CRUD for customers.
 */
class CustomerController extends AbstractController
{
    /**
     * @Route("/", name="oro_customer_customer_index")
     * @Template
     * @AclAncestor("oro_customer_customer_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => Customer::class
        ];
    }

    /**
     * @Route("/view/{id}", name="oro_customer_customer_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_customer_customer_view",
     *      type="entity",
     *      class="OroCustomerBundle:Customer",
     *      permission="VIEW"
     * )
     *
     * @param Customer $customer
     * @return array
     */
    public function viewAction(Customer $customer)
    {
        return [
            'entity' => $customer,
        ];
    }

    /**
     * @Route("/create", name="oro_customer_customer_create")
     * @Template("OroCustomerBundle:Customer:update.html.twig")
     * @Acl(
     *      id="oro_customer_create",
     *      type="entity",
     *      class="OroCustomerBundle:Customer",
     *      permission="CREATE"
     * )
     *
     * @return array
     */
    public function createAction()
    {
        return $this->update(new Customer());
    }

    /**
     * @Route("/update/{id}", name="oro_customer_customer_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_customer_customer_update",
     *      type="entity",
     *      class="OroCustomerBundle:Customer",
     *      permission="EDIT"
     * )
     *
     * @param Customer $customer
     * @return array
     */
    public function updateAction(Customer $customer)
    {
        return $this->update($customer);
    }

    /**
     * @param Customer $customer
     * @return array|RedirectResponse
     */
    protected function update(Customer $customer)
    {
        return $this->get(UpdateHandler::class)->handleUpdate(
            $customer,
            $this->createForm(CustomerType::class, $customer),
            function (Customer $customer) {
                return [
                    'route' => 'oro_customer_customer_update',
                    'parameters' => ['id' => $customer->getId()],
                ];
            },
            function (Customer $customer) {
                return [
                    'route' => 'oro_customer_customer_view',
                    'parameters' => ['id' => $customer->getId()],
                ];
            },
            $this->get(TranslatorInterface::class)->trans('oro.customer.controller.customer.saved.message')
        );
    }

    /**
     * @Route("/info/{id}", name="oro_customer_customer_info", requirements={"id"="\d+"})
     * @Template("OroCustomerBundle:Customer/widget:info.html.twig")
     * @AclAncestor("oro_customer_customer_view")
     *
     * @param Customer $customer
     * @return array
     */
    public function infoAction(Customer $customer)
    {
        return [
            'entity' => $customer,
            'treeData' => $this->get(CustomerTreeHandler::class)->createTree($customer),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                UpdateHandler::class,
                CustomerTreeHandler::class,
            ]
        );
    }
}
