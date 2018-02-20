<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendCustomerUserRoleSelectType extends AbstractType
{
    const NAME = 'oro_customer_frontend_customer_user_role_select';

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var ManagerRegistry */
    protected $registry;

    /** @var string */
    protected $roleClass;

    /** @var AclHelper */
    protected $aclHelper;

    /**
     * @param TokenAccessorInterface $tokenAccessor
     * @param ManagerRegistry        $registry
     * @param AclHelper              $aclHelper
     */
    public function __construct(TokenAccessorInterface $tokenAccessor, ManagerRegistry $registry, AclHelper $aclHelper)
    {
        $this->tokenAccessor = $tokenAccessor;
        $this->registry = $registry;
        $this->aclHelper = $aclHelper;
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
        return CustomerUserRoleSelectType::NAME;
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

        $resolver->setNormalizer('loader', function () use ($loggedUser) {
            /** @var $repo CustomerUserRoleRepository */
            $repo = $this->registry->getManagerForClass($this->roleClass)
                ->getRepository($this->roleClass);
            $criteria = new Criteria();
            $qb = $repo->createQueryBuilder('customer');
            $this->aclHelper->applyAclToCriteria(
                $this->roleClass,
                $criteria,
                'ASSIGN',
                ['customer' => 'customer.customer', 'organization' => 'customer.organization']
            );
            $qb->addCriteria($criteria);
            $qb->orWhere(
                'customer.selfManaged = :isActive AND customer.public = :isActive AND customer.customer is NULL'
            );
            $qb->setParameter('isActive', true, \PDO::PARAM_BOOL);

            return new ORMQueryBuilderLoader($qb);
        });
    }

    /**
     * @param string $roleClass
     */
    public function setRoleClass($roleClass)
    {
        $this->roleClass = $roleClass;
    }
}
