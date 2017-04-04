<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerRepository;

class UniqueCustomerNameConstraintValidator extends ConstraintValidator
{
    /**
     * @var CustomerRepository
     */
    protected $repository;

    /**
     * @param CustomerRepository $repository
     */
    public function __construct(CustomerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     * @param Customer $value
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value === null) {
            return;
        }

        if ($value instanceof Customer) {
            $customerName = $value->getName();
        } else {
            $customerName = (string) $value;
        }

        if ($this->repository->countByName($customerName) > 1) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%name%', $customerName)
                ->addViolation();
        }
    }
}
