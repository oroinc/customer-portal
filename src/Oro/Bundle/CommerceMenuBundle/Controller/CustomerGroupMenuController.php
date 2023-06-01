<?php

namespace Oro\Bundle\CommerceMenuBundle\Controller;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider;
use Oro\Bundle\OrganizationBundle\Provider\ScopeOrganizationCriteriaProvider;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The controller for the customer group menu.
 * @Route("/menu/customer-group")
 */
class CustomerGroupMenuController extends AbstractFrontendMenuController
{
    /**
     * @Route("/{id}", name="oro_commerce_menu_customer_group_menu_index")
     * @Template
     *
     * @param CustomerGroup $customerGroup
     * @return array
     */
    public function indexAction(CustomerGroup $customerGroup)
    {
        $this->denyAccessUnlessGranted('oro_navigation_manage_menus');
        $contexts = $this->get('oro_commerce_menu.menu_context_provider.customer_group')->getContexts($customerGroup);

        return [
            'entity' => $customerGroup,
            'contexts' => $contexts,
            'entityClass' => $this->getEntityClass(),
        ];
    }

    /**
     * @Route(
     *      "/context/",
     *      name="oro_commerce_menu_customer_group_menu_context_index",
     *      requirements={"id"="\d+"}
     * )
     * @Template("OroCommerceMenuBundle:CustomerGroupMenu/widget:contextIndex.html.twig")
     * @param Request $request
     * @return array
     */
    public function contextIndexAction(Request $request)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());
        $this->checkAcl($context);

        return parent::index($context);
    }

    /**
     * @Route("/{menuName}/view", name="oro_commerce_menu_customer_group_menu_view")
     * @Template
     *
     * @param string  $menuName
     * @param Request $request
     * @return array
     */
    public function viewAction(Request $request, $menuName)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return parent::view($menuName, $context);
    }

    /**
     * @Route("/{menuName}/create/{parentKey}", name="oro_commerce_menu_customer_group_menu_create")
     * @Template("OroCommerceMenuBundle:CustomerGroupMenu:update.html.twig")
     * @param Request     $request
     * @param string      $menuName
     * @param string|null $parentKey
     * @return array|RedirectResponse
     */
    public function createAction(Request $request, $menuName, $parentKey = null)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return parent::create($menuName, $parentKey, $context);
    }

    /**
     * @Route("/{menuName}/update/{key}", name="oro_commerce_menu_customer_group_menu_update")
     * @Template
     *
     * @param Request $request
     * @param string  $menuName
     * @param string  $key
     * @return array|RedirectResponse
     */
    public function updateAction(Request $request, $menuName, $key)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return parent::update($menuName, $key, $context);
    }

    /**
     * @Route("/{menuName}/move", name="oro_commerce_menu_customer_group_menu_move")
     *
     * @param Request $request
     * @param string  $menuName
     *
     * @return array|RedirectResponse
     */
    public function moveAction(Request $request, $menuName)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return parent::move($request, $menuName, $context);
    }

    /**
     * {@inheritDoc}
     */
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

    /**
     * {@inheritDoc}
     */
    protected function getMenu($menuName, array $context)
    {
        if (array_key_exists(ScopeCustomerGroupCriteriaProvider::CUSTOMER_GROUP, $context)) {
            /** @var CustomerGroup $customerGroup */
            $customerGroup = $context[ScopeCustomerGroupCriteriaProvider::CUSTOMER_GROUP];
            $context[ScopeOrganizationCriteriaProvider::ORGANIZATION] = $customerGroup->getOrganization();
        }

        return parent::getMenu($menuName, $context);
    }
}
