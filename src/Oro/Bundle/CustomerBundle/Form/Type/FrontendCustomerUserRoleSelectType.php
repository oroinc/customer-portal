<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Acl\AccessRule\SelfManagedPublicCustomerUserRoleAccessRule;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type that allows to select roles for customer user.
 */
class FrontendCustomerUserRoleSelectType extends AbstractType
{
    const NAME = 'oro_customer_frontend_customer_user_role_select';

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var ManagerRegistry */
    protected $registry;

    /** @var string */
    protected $roleClass;

    /**
     * @param TokenAccessorInterface $tokenAccessor
     * @param ManagerRegistry        $registry
     */
    public function __construct(TokenAccessorInterface $tokenAccessor, ManagerRegistry $registry)
    {
        $this->tokenAccessor = $tokenAccessor;
        $this->registry = $registry;
    }

    /**
     * @return ManagerRegistry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CustomerUserRoleSelectType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $loggedUser = $this->tokenAccessor->getUser();
        if (!$loggedUser instanceof CustomerUser) {
            return;
        }

        $resolver->setDefaults([
            'query_builder' => function () {
                /** @var $repo CustomerUserRoleRepository */
                $repo = $this->registry->getManagerForClass($this->roleClass)->getRepository($this->roleClass);
                return $repo->createQueryBuilder('customer');
            },
            'acl_options' => [
                'permission' => 'ASSIGN',
                'options' => [SelfManagedPublicCustomerUserRoleAccessRule::ENABLE_RULE => true]
            ]
        ]);
    }

    /**
     * @param string $roleClass
     */
    public function setRoleClass($roleClass)
    {
        $this->roleClass = $roleClass;
    }
}
