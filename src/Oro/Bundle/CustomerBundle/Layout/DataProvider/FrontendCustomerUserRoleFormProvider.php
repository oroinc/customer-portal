<?php

namespace Oro\Bundle\CustomerBundle\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserRoleUpdateFrontendHandler;
use Oro\Bundle\LayoutBundle\Layout\DataProvider\AbstractFormProvider;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FrontendCustomerUserRoleFormProvider extends AbstractFormProvider
{
    const CUSTOMER_USER_ROLE_CREATE_ROUTE_NAME = 'oro_customer_frontend_customer_user_role_create';
    const CUSTOMER_USER_ROLE_UPDATE_ROUTE_NAME = 'oro_customer_frontend_customer_user_role_update';

    /** @var CustomerUserRoleUpdateFrontendHandler */
    protected $handler;

    /**
     * @param FormFactoryInterface                 $formFactory
     * @param CustomerUserRoleUpdateFrontendHandler $handler
     * @param UrlGeneratorInterface                $router
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        CustomerUserRoleUpdateFrontendHandler $handler,
        UrlGeneratorInterface $router
    ) {
        parent::__construct($formFactory, $router);

        $this->handler = $handler;
    }

    /**
     * Get form accessor with customer user role form
     *
     * @param CustomerUserRole $customerUserRole
     *
     * @return FormView
     */
    public function getRoleFormView(CustomerUserRole $customerUserRole)
    {
        return $this->getFormView('', $customerUserRole, $this->getRoleOptions($customerUserRole));
    }

    /**
     * @param CustomerUserRole $customerUserRole
     * @return FormInterface
     */
    public function getRoleForm(CustomerUserRole $customerUserRole)
    {
        return $this->getForm('', $customerUserRole, $this->getRoleOptions($customerUserRole));
    }

    /**
     * @param CustomerUserRole $customerUserRole
     * @return array
     */
    public function getRoleOptions(CustomerUserRole $customerUserRole)
    {
        $options = [];
        if ($customerUserRole->getId()) {
            $options['action'] = $this->generateUrl(
                self::CUSTOMER_USER_ROLE_UPDATE_ROUTE_NAME,
                ['id' => $customerUserRole->getId()]
            );
        } else {
            $options['action'] = $this->generateUrl(
                self::CUSTOMER_USER_ROLE_CREATE_ROUTE_NAME
            );
        }
        return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function createForm($formName, $data = null, array $options = [])
    {
        $form = $this->handler->createForm($data);
        $this->handler->process($data);

        return $form;
    }
}
