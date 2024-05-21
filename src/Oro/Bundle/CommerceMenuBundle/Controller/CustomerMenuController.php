<?php

namespace Oro\Bundle\CommerceMenuBundle\Controller;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Menu\ContextProvider\CustomerMenuContextProvider;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerCriteriaProvider;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider;
use Oro\Bundle\OrganizationBundle\Provider\ScopeOrganizationCriteriaProvider;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The controller for the customer menu.
 */
#[Route(path: '/menu/customer')]
class CustomerMenuController extends AbstractFrontendMenuController
{
    /**
     * @param Customer $customer
     * @return array
     */
    #[Route(
        path: '/{id}',
        name: 'oro_commerce_menu_customer_menu_index',
        requirements: ['id' => '\d+']
    )]
    #[Template]
    public function indexAction(Customer $customer)
    {
        $this->denyAccessUnlessGranted('oro_navigation_manage_menus');
        $contexts = $this->container->get(CustomerMenuContextProvider::class)->getContexts($customer);

        return [
            'entity' => $customer,
            'contexts' => $contexts,
            'entityClass' => $this->getEntityClass(),
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    #[Route(path: '/context/', name: 'oro_commerce_menu_customer_menu_context_index', requirements: ['id' => '\d+'])]
    #[Template('@OroCommerceMenu/CustomerMenu/widget/contextIndex.html.twig')]
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
    #[Route(path: '/{menuName}/view', name: 'oro_commerce_menu_customer_menu_view')]
    #[Template('@OroCommerceMenu/CustomerMenu/update.html.twig')]
    public function viewAction($menuName, Request $request)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return $this->update($menuName, null, $context);
    }

    /**
     *
     * @param Request     $request
     * @param string      $menuName
     * @param string|null $parentKey
     * @return array|RedirectResponse
     */
    #[Route(path: '/{menuName}/create/{parentKey}', name: 'oro_commerce_menu_customer_menu_create')]
    #[Template('@OroCommerceMenu/CustomerMenu/update.html.twig')]
    public function createAction(Request $request, $menuName, $parentKey = null)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return parent::create($menuName, $parentKey, $context);
    }

    /**
     *
     * @return array|RedirectResponse
     */
    #[Route(path: '/{menuName}/update/{key}', name: 'oro_commerce_menu_customer_menu_update')]
    #[Template]
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
    #[Route(path: '/{menuName}/move', name: 'oro_commerce_menu_customer_menu_move')]
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
            'oro_customer_customer_update',
            $context[ScopeCustomerCriteriaProvider::CUSTOMER]
        )
        ) {
            throw $this->createAccessDeniedException();
        }
        parent::checkAcl($context);
    }

    /**
     * {@inheritDoc}
     */
    protected function getMenu(string $menuName, array $context): ItemInterface
    {
        if (array_key_exists(ScopeCustomerCriteriaProvider::CUSTOMER, $context)) {
            /** @var Customer $customer */
            $customer = $context[ScopeCustomerCriteriaProvider::CUSTOMER];
            $context[ScopeOrganizationCriteriaProvider::ORGANIZATION] = $customer->getOrganization();
            $context[ScopeCustomerGroupCriteriaProvider::CUSTOMER_GROUP] = $customer->getGroup();
        }

        return parent::getMenu($menuName, $context);
    }

    /**
     * @return array
     */
    protected function getAllowedContextKeys()
    {
        return [ScopeCustomerCriteriaProvider::CUSTOMER, ScopeCriteriaProvider::WEBSITE];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                CustomerMenuContextProvider::class,
            ]
        );
    }
}
