<?php

namespace Oro\Bundle\CommerceMenuBundle\Controller;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Menu\ContextProvider\CustomerGroupMenuContextProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider;
use Oro\Bundle\OrganizationBundle\Provider\ScopeOrganizationCriteriaProvider;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The controller for the customer group menu.
 */
#[Route(path: '/menu/customer-group')]
class CustomerGroupMenuController extends AbstractFrontendMenuController
{
    /**
     * @param CustomerGroup $customerGroup
     * @return array
     */
    #[Route(
        path: '/{id}',
        name: 'oro_commerce_menu_customer_group_menu_index',
        requirements: ['id' => '\d+']
    )]
    #[Template('@OroCommerceMenu/CustomerGroupMenu/index.html.twig')]
    public function indexAction(CustomerGroup $customerGroup)
    {
        $this->denyAccessUnlessGranted('oro_navigation_manage_menus');
        $contexts = $this->container->get(CustomerGroupMenuContextProvider::class)->getContexts($customerGroup);

        return [
            'entity' => $customerGroup,
            'contexts' => $contexts,
            'entityClass' => $this->getEntityClass(),
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    #[Route(
        path: '/context/',
        name: 'oro_commerce_menu_customer_group_menu_context_index',
        requirements: ['id' => '\d+']
    )]
    #[Template('@OroCommerceMenu/CustomerGroupMenu/widget/contextIndex.html.twig')]
    public function contextIndexAction(Request $request)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());
        $this->checkAcl($context);

        return parent::index($context);
    }

    /**
     *
     * @param string  $menuName
     * @param Request $request
     * @return array
     */
    #[Route(path: '/{menuName}/view', name: 'oro_commerce_menu_customer_group_menu_view')]
    #[Template('@OroCommerceMenu/CustomerGroupMenu/update.html.twig')]
    public function viewAction(Request $request, $menuName)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return $this->update($menuName, null, $context);
    }

    /**
     * @param Request     $request
     * @param string      $menuName
     * @param string|null $parentKey
     * @return array|RedirectResponse
     */
    #[Route(path: '/{menuName}/create/{parentKey}', name: 'oro_commerce_menu_customer_group_menu_create')]
    #[Template('@OroCommerceMenu/CustomerGroupMenu/update.html.twig')]
    public function createAction(Request $request, $menuName, $parentKey = null)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return parent::create($menuName, $parentKey, $context);
    }

    /**
     *
     * @return array|RedirectResponse
     */
    #[Route(path: '/{menuName}/update/{key}', name: 'oro_commerce_menu_customer_group_menu_update')]
    #[Template('@OroCommerceMenu/CustomerGroupMenu/update.html.twig')]
    public function updateAction(Request $request, string $menuName, ?string $key = null)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return parent::update($menuName, $key, $context);
    }

    /**
     *
     * @param Request $request
     * @param string  $menuName
     * @return array|RedirectResponse
     */
    #[Route(path: '/{menuName}/move', name: 'oro_commerce_menu_customer_group_menu_move')]
    public function moveAction(Request $request, $menuName)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return parent::move($request, $menuName, $context);
    }

    #[\Override]
    protected function checkAcl(array $context)
    {
        if (!$this->isGranted(
            'oro_customer_customer_group_update',
            $context[ScopeCustomerGroupCriteriaProvider::CUSTOMER_GROUP]
        )
        ) {
            throw $this->createAccessDeniedException();
        }
        parent::checkAcl($context);
    }

    /**
     * @return array
     */
    protected function getAllowedContextKeys()
    {
        return [ScopeCustomerGroupCriteriaProvider::CUSTOMER_GROUP, ScopeCriteriaProvider::WEBSITE];
    }

    #[\Override]
    protected function getMenu(string $menuName, array $context): ItemInterface
    {
        if (array_key_exists(ScopeCustomerGroupCriteriaProvider::CUSTOMER_GROUP, $context)) {
            /** @var CustomerGroup $customerGroup */
            $customerGroup = $context[ScopeCustomerGroupCriteriaProvider::CUSTOMER_GROUP];
            $context[ScopeOrganizationCriteriaProvider::ORGANIZATION] = $customerGroup->getOrganization();
        }

        return parent::getMenu($menuName, $context);
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                CustomerGroupMenuContextProvider::class,
            ]
        );
    }
}
