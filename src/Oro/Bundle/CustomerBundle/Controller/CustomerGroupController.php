<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerGroupHandler;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerGroupType;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD for customer groups.
 */
class CustomerGroupController extends AbstractController
{
    /**
     * @Route("/", name="oro_customer_customer_group_index")
     * @Template
     * @AclAncestor("oro_customer_customer_group_view")
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => CustomerGroup::class
        ];
    }

    /**
     * @Route("/view/{id}", name="oro_customer_customer_group_view", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_customer_customer_group_view",
     *      type="entity",
     *      class="OroCustomerBundle:CustomerGroup",
     *      permission="VIEW"
     * )
     * @Template()
     */
    public function viewAction(CustomerGroup $group): array
    {
        return [
            'entity' => $group
        ];
    }

    /**
     * @Route("/create", name="oro_customer_customer_group_create")
     * @Template("@OroCustomer/CustomerGroup/update.html.twig")
     * @Acl(
     *      id="oro_customer_customer_group_create",
     *      type="entity",
     *      class="OroCustomerBundle:CustomerGroup",
     *      permission="CREATE"
     * )
     */
    public function createAction(Request $request): array|RedirectResponse
    {
        return $this->update($request, new CustomerGroup());
    }

    /**
     * @Route("/update/{id}", name="oro_customer_customer_group_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_customer_customer_group_update",
     *      type="entity",
     *      class="OroCustomerBundle:CustomerGroup",
     *      permission="EDIT"
     * )
     */
    public function updateAction(Request $request, CustomerGroup $group): array|RedirectResponse
    {
        return $this->update($request, $group);
    }

    protected function update(Request $request, CustomerGroup $group): array|RedirectResponse
    {
        $form = $this->createForm(CustomerGroupType::class, $group);
        $handler = new CustomerGroupHandler(
            $this->getDoctrine()->getManagerForClass(ClassUtils::getClass($group)),
            $this->get(EventDispatcherInterface::class)
        );

        return $this->get(UpdateHandlerFacade::class)->update(
            $group,
            $form,
            $this->get(TranslatorInterface::class)->trans('oro.customer.controller.customergroup.saved.message'),
            $request,
            $handler
        );
    }

    /**
     * @Route("/info/{id}", name="oro_customer_customer_group_info", requirements={"id"="\d+"})
     * @Template("@OroCustomer/CustomerGroup/widget/info.html.twig")
     * @AclAncestor("oro_customer_customer_group_view")
     */
    public function infoAction(CustomerGroup $group): array
    {
        return [
            'entity' => $group
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
                EventDispatcherInterface::class,
                UpdateHandlerFacade::class
            ]
        );
    }
}
