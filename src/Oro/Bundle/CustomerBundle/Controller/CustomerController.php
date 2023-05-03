<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerType;
use Oro\Bundle\CustomerBundle\JsTree\CustomerTreeHandler;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\FormBundle\Provider\FormTemplateDataProviderInterface;
use Oro\Bundle\FormBundle\Provider\SaveAndReturnActionFormTemplateDataProvider;
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
     */
    public function indexAction(): array
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
     */
    public function viewAction(Customer $customer): array
    {
        return [
            'entity' => $customer,
        ];
    }

    /**
     * @Route("/create", name="oro_customer_customer_create")
     * @Template("@OroCustomer/Customer/update.html.twig")
     * @Acl(
     *      id="oro_customer_create",
     *      type="entity",
     *      class="OroCustomerBundle:Customer",
     *      permission="CREATE"
     * )
     */
    public function createAction(): array|RedirectResponse
    {
        return $this->update(new Customer());
    }

    /**
     * @Route(
     *     "/create/subsidiary/{parentCustomer}",
     *     name="oro_customer_customer_create_subsidiary",
     *     requirements={"parentCustomer"="\d+"}
     * )
     * @Template("@OroCustomer/Customer/update.html.twig")
     * @AclAncestor("oro_customer_create")
     */
    public function createSubsidiaryAction(Customer $parentCustomer): array|RedirectResponse
    {
        if (!$this->isGranted('VIEW', $parentCustomer)) {
            throw $this->createAccessDeniedException();
        }

        $customer = new Customer();
        $customer->setParent($parentCustomer);

        $saveAndReturnActionFormTemplateDataProvider = $this->get(SaveAndReturnActionFormTemplateDataProvider::class);
        $saveAndReturnActionFormTemplateDataProvider
            ->setSaveFormActionRoute(
                'oro_customer_customer_create_subsidiary',
                [
                    'parentCustomer' => $parentCustomer->getId(),
                ]
            )
            ->setReturnActionRoute(
                'oro_customer_customer_view',
                [
                    'id' => $parentCustomer->getId(),
                ],
                'oro_customer_customer_view'
            );

        return $this->update($customer, $saveAndReturnActionFormTemplateDataProvider);
    }

    /**
     * @Route(
     *     "/create/customer-group/{group}",
     *     name="oro_customer_customer_create_for_customer_group",
     *     requirements={"group"="\d+"}
     * )
     * @Template("@OroCustomer/Customer/update.html.twig")
     * @AclAncestor("oro_customer_create")
     */
    public function createForCustomerGroupAction(CustomerGroup $group): array|RedirectResponse
    {
        if (!$this->isGranted('VIEW', $group)) {
            throw $this->createAccessDeniedException();
        }

        $customer = new Customer();
        $customer->setGroup($group);

        $saveAndReturnActionFormTemplateDataProvider = $this->get(SaveAndReturnActionFormTemplateDataProvider::class);
        $saveAndReturnActionFormTemplateDataProvider
            ->setSaveFormActionRoute(
                'oro_customer_customer_create_for_customer_group',
                [
                    'group' => $group->getId(),
                ]
            )
            ->setReturnActionRoute(
                'oro_customer_customer_group_view',
                [
                    'id' => $group->getId(),
                ],
                'oro_customer_customer_group_view'
            );

        return $this->update($customer, $saveAndReturnActionFormTemplateDataProvider);
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
     */
    public function updateAction(Customer $customer): array|RedirectResponse
    {
        return $this->update($customer);
    }

    protected function update(
        Customer $customer,
        FormTemplateDataProviderInterface|null $resultProvider = null
    ): array|RedirectResponse {
        return $this->get(UpdateHandlerFacade::class)->update(
            $customer,
            $this->createForm(CustomerType::class, $customer),
            $this->get(TranslatorInterface::class)->trans('oro.customer.controller.customer.saved.message'),
            null,
            null,
            $resultProvider
        );
    }

    /**
     * @Route("/info/{id}", name="oro_customer_customer_info", requirements={"id"="\d+"})
     * @Template("@OroCustomer/Customer/widget/info.html.twig")
     * @AclAncestor("oro_customer_customer_view")
     */
    public function infoAction(Customer $customer): array
    {
        return [
            'entity' => $customer,
            'treeData' => $this->get(CustomerTreeHandler::class)->createTree($customer),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                CustomerTreeHandler::class,
                UpdateHandlerFacade::class,
                SaveAndReturnActionFormTemplateDataProvider::class,
            ]
        );
    }
}
