<?php

namespace Oro\Bundle\CustomerBundle\Layout\DataProvider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRegistrationType;
use Oro\Bundle\LayoutBundle\Layout\DataProvider\AbstractFormProvider;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Returns CustomerUser register form
 */
class FrontendCustomerUserRegistrationFormProvider extends AbstractFormProvider
{
    const ACCOUNT_USER_REGISTER_ROUTE_NAME = 'oro_customer_frontend_customer_user_register';

    /** @var ConfigManager */
    private $configManager;

    /** @var WebsiteManager */
    private $websiteManager;

    /** @var ManagerRegistry */
    private $doctrine;

    public function __construct(
        FormFactoryInterface $formFactory,
        ConfigManager $configManager,
        WebsiteManager $websiteManager,
        ManagerRegistry $doctrine,
        UrlGeneratorInterface $router
    ) {
        parent::__construct($formFactory, $router);

        $this->configManager = $configManager;
        $this->websiteManager = $websiteManager;
        $this->doctrine = $doctrine;
    }

    /**
     * @return FormView
     */
    public function getRegisterFormView()
    {
        $customerUser = $this->createCustomerUser();

        $options['action'] = $this->generateUrl(self::ACCOUNT_USER_REGISTER_ROUTE_NAME);

        return $this->getFormView(FrontendCustomerUserRegistrationType::class, $customerUser, $options);
    }

    /**
     * @return FormInterface
     */
    public function getRegisterForm()
    {
        $customerUser = $this->createCustomerUser();

        $options['action'] = $this->generateUrl(self::ACCOUNT_USER_REGISTER_ROUTE_NAME);

        return $this->getForm(FrontendCustomerUserRegistrationType::class, $customerUser, $options);
    }

    /**
     * @return CustomerUser
     */
    public function createCustomerUser()
    {
        $customerUser = new CustomerUser();

        $defaultOwnerId = $this->configManager->get('oro_customer.default_customer_owner');
        if (!$defaultOwnerId) {
            throw new \RuntimeException('Application Owner is empty');
        }

        $website = $this->websiteManager->getCurrentWebsite();
        if (!$website) {
            throw new \RuntimeException('Website is empty');
        }

        /** @var Organization $organization */
        $organization = $website->getOrganization();
        if (!$organization) {
            throw new \RuntimeException('Website organization is empty');
        }

        /** @var CustomerUserRole $defaultRole */
        $defaultRole = $website->getDefaultRole();

        if (!$defaultRole) {
            throw new \RuntimeException(sprintf('Role "%s" was not found', CustomerUser::ROLE_DEFAULT));
        }

        /** @var User $owner */
        $owner = $this->doctrine->getManagerForClass(User::class)->find(User::class, $defaultOwnerId);

        $customerUser
            ->setOwner($owner)
            ->setOrganization($organization)
            ->setWebsite($website)
            ->addUserRole($defaultRole);

        return $customerUser;
    }
}
