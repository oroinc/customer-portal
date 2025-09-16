<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerGroupHandler;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerGroupType;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD for customer groups.
 */
class CustomerGroupController extends AbstractController
{
    #[Route(path: '/', name: 'oro_customer_customer_group_index')]
    #[Template('@OroCustomer/CustomerGroup/index.html.twig')]
    #[AclAncestor('oro_customer_customer_group_view')]
    public function indexAction(): array
    {
        return [
            'entity_class' => CustomerGroup::class
        ];
    }

    #[Route(path: '/view/{id}', name: 'oro_customer_customer_group_view', requirements: ['id' => '\d+'])]
    #[Template('@OroCustomer/CustomerGroup/view.html.twig')]
    #[Acl(id: 'oro_customer_customer_group_view', type: 'entity', class: CustomerGroup::class, permission: 'VIEW')]
    public function viewAction(CustomerGroup $group): array
    {
        return [
            'entity' => $group
        ];
    }

    #[Route(path: '/create', name: 'oro_customer_customer_group_create')]
    #[Template('@OroCustomer/CustomerGroup/update.html.twig')]
    #[Acl(id: 'oro_customer_customer_group_create', type: 'entity', class: CustomerGroup::class, permission: 'CREATE')]
    public function createAction(Request $request): array|RedirectResponse
    {
        return $this->update($request, new CustomerGroup());
    }

    #[Route(path: '/update/{id}', name: 'oro_customer_customer_group_update', requirements: ['id' => '\d+'])]
    #[Template('@OroCustomer/CustomerGroup/update.html.twig')]
    #[Acl(id: 'oro_customer_customer_group_update', type: 'entity', class: CustomerGroup::class, permission: 'EDIT')]
    public function updateAction(Request $request, CustomerGroup $group): array|RedirectResponse
    {
        return $this->update($request, $group);
    }

    protected function update(Request $request, CustomerGroup $group): array|RedirectResponse
    {
        $form = $this->createForm(CustomerGroupType::class, $group);
        $handler = new CustomerGroupHandler(
            $this->container->get('doctrine')->getManagerForClass(ClassUtils::getClass($group)),
            $this->container->get(EventDispatcherInterface::class)
        );

        return $this->container->get(UpdateHandlerFacade::class)->update(
            $group,
            $form,
            $this->container->get(TranslatorInterface::class)
                ->trans('oro.customer.controller.customergroup.saved.message'),
            $request,
            $handler
        );
    }

    #[Route(path: '/info/{id}', name: 'oro_customer_customer_group_info', requirements: ['id' => '\d+'])]
    #[Template('@OroCustomer/CustomerGroup/widget/info.html.twig')]
    #[AclAncestor('oro_customer_customer_group_view')]
    public function infoAction(CustomerGroup $group): array
    {
        return [
            'entity' => $group
        ];
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                EventDispatcherInterface::class,
                UpdateHandlerFacade::class,
                'doctrine' => ManagerRegistry::class
            ]
        );
    }
}
