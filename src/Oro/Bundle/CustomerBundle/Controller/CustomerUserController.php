<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserHandler;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserType;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Back-office CRUD for customer users.
 */
class CustomerUserController extends AbstractController
{
    /**
     * @Route("/view/{id}", name="oro_customer_customer_user_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_customer_customer_user_view",
     *      type="entity",
     *      class="OroCustomerBundle:CustomerUser",
     *      permission="VIEW"
     * )
     */
    public function viewAction(CustomerUser $customerUser): array
    {
        return [
            'entity' => $customerUser
        ];
    }

    /**
     * @Route("/", name="oro_customer_customer_user_index")
     * @Template
     * @AclAncestor("oro_customer_customer_user_view")
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => CustomerUser::class
        ];
    }

    /**
     * @Route("/login-attempts", name="oro_customer_login_attempts")
     * @Template
     * @AclAncestor("oro_customer_view_user_login_attempt")
     */
    public function loginAttemptsAction(): array
    {
        return [];
    }

    /**
     * @Route("/info/{id}", name="oro_customer_customer_user_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("oro_customer_customer_user_view")
     */
    public function infoAction(CustomerUser $customerUser): array
    {
        return [
            'entity' => $customerUser
        ];
    }

    /**
     * @Route("/get-roles/{customerUserId}/{customerId}",
     *      name="oro_customer_customer_user_roles",
     *      requirements={"customerId"="\d+", "customerUserId"="\d+"},
     *      defaults={"customerId"=0, "customerUserId"=0}
     * )
     * @Template("@OroCustomer/CustomerUser/widget/roles.html.twig")
     * @AclAncestor("oro_customer_customer_user_view")
     */
    public function getRolesAction(Request $request, string $customerUserId, string $customerId): array
    {
        /** @var DoctrineHelper $doctrineHelper */
        $doctrineHelper = $this->get(DoctrineHelper::class);

        if ($customerUserId) {
            $customerUser = $doctrineHelper->getEntityReference(CustomerUser::class, $customerUserId);
        } else {
            $customerUser = new CustomerUser();
        }

        $customer = null;
        if ($customerId) {
            $customer = $doctrineHelper->getEntityReference(
                Customer::class,
                $customerId
            );
        }
        $customerUser->setCustomer($customer);

        $form = $this->createForm(CustomerUserType::class, $customerUser);
        $form->handleRequest($this->get(RequestStack::class)->getMainRequest());

        if (($error = $request->get('error', '')) && $form->has('userRoles')) {
            $form
                ->get('userRoles')
                ->addError(new FormError((string)$error));
        }

        return ['form' => $form->createView()];
    }

    /**
     * Create customer user form
     *
     * @Route("/create", name="oro_customer_customer_user_create")
     * @Template("@OroCustomer/CustomerUser/update.html.twig")
     * @Acl(
     *      id="oro_customer_customer_user_create",
     *      type="entity",
     *      class="OroCustomerBundle:CustomerUser",
     *      permission="CREATE"
     * )
     */
    public function createAction(Request $request): array|RedirectResponse
    {
        return $this->update(new CustomerUser(), $request);
    }

    /**
     * Edit customer user form
     *
     * @Route("/update/{id}", name="oro_customer_customer_user_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_customer_customer_user_update",
     *      type="entity",
     *      class="OroCustomerBundle:CustomerUser",
     *      permission="EDIT"
     * )
     */
    public function updateAction(CustomerUser $customerUser, Request $request): array|RedirectResponse
    {
        return $this->update($customerUser, $request);
    }

    protected function update(CustomerUser $customerUser, Request $request): array|RedirectResponse
    {
        $form = $this->createForm(CustomerUserType::class, $customerUser);
        $handler = new CustomerUserHandler(
            $this->get(CustomerUserManager::class),
            $this->get(TokenAccessorInterface::class),
            $this->get(TranslatorInterface::class),
            $this->get(LoggerInterface::class)
        );

        return $this->get(UpdateHandlerFacade::class)->update(
            $customerUser,
            $form,
            $this->get(TranslatorInterface::class)->trans('oro.customer.controller.customeruser.saved.message'),
            $request,
            $handler
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                DoctrineHelper::class,
                TranslatorInterface::class,
                TokenAccessorInterface::class,
                CustomerUserManager::class,
                LoggerInterface::class,
                RequestStack::class,
                UpdateHandlerFacade::class
            ]
        );
    }
}
