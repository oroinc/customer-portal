<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Handler\CustomerUserReassignUpdaterInterface;
use Oro\Bundle\EntityBundle\Provider\EntityClassNameProviderInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates Customer User editing when we are trying to change Customer User's Customer, which leads
 * to updating Customer User's related entities (such as Orders, Quotes, Shopping Lists etc) in case the user
 * doesn't have permissions to edit these related entities
 */
class CustomerRelatedEntitiesValidator extends ConstraintValidator
{
    private AuthorizationCheckerInterface $authorizationChecker;
    private CustomerUserReassignUpdaterInterface $customerUserReassignUpdater;
    private ManagerRegistry $doctrine;
    private EntityClassNameProviderInterface $entityClassNameProvider;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        CustomerUserReassignUpdaterInterface $customerUserReassignUpdater,
        ManagerRegistry $doctrine,
        EntityClassNameProviderInterface $entityClassNameProvider
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->customerUserReassignUpdater = $customerUserReassignUpdater;
        $this->doctrine = $doctrine;
        $this->entityClassNameProvider = $entityClassNameProvider;
    }

    /**
     * @param CustomerUser $customerUser
     * @param CustomerRelatedEntities $constraint
     */
    public function validate($customerUser, Constraint $constraint)
    {
        if (!$customerUser instanceof CustomerUser) {
            throw new UnexpectedTypeException($customerUser, CustomerUser::class);
        }

        if (!$customerUser->getId()) {
            return;
        }

        $em = $this->doctrine->getManagerForClass(CustomerUser::class);

        /** @var array $originalCustomerUser */
        $originalCustomerUser = $em->getUnitOfWork()
            ->getOriginalEntityData($customerUser);

        if (!isset($originalCustomerUser['customer'])
            || $originalCustomerUser['customer'] === $customerUser->getCustomer()
        ) {
            return;
        }

        $entityClassesToUpdate = $this->customerUserReassignUpdater->getClassNamesToUpdate(
            $customerUser
        );

        $restrictions = [];
        foreach ($entityClassesToUpdate as $entityClass) {
            if (!$this->authorizationChecker->isGranted(sprintf(
                '%s;entity:%s',
                'EDIT',
                $entityClass
            ))) {
                $restrictions[] = $entityClass;
            }
        }

        array_walk($restrictions, function (&$className) {
            $className = $this->entityClassNameProvider->getEntityClassName($className);
        });

        if ($restrictions) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ entityNames }}', implode(', ', $restrictions))
                ->atPath('customer')
                ->addViolation();
        }
    }
}
