<?php

namespace Oro\Bundle\CustomerBundle\Condition;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Handler\CustomerAssignHelper;
use Oro\Component\Action\Condition\AbstractCondition;
use Oro\Component\ConfigExpression\ContextAccessorAwareInterface;
use Oro\Component\ConfigExpression\ContextAccessorAwareTrait;
use Oro\Component\ConfigExpression\Exception\InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * Condition checks that passed Customer entity is assigned to another entities.
 * That check required before deletion customer.
 */
class CustomerHasAssignments extends AbstractCondition implements ContextAccessorAwareInterface
{
    use ContextAccessorAwareTrait;

    const NAME = 'customer_has_assignments';

    /** @var CustomerAssignHelper */
    protected $helper;

    /** @var mixed */
    protected $customer;

    public function __construct(CustomerAssignHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(array $options)
    {
        if (count($options) !== 1) {
            throw new InvalidArgumentException('Customer parameter is required');
        }

        $this->customer = reset($options);

        if (!$this->customer instanceof PropertyPathInterface && !$this->customer instanceof Customer) {
            throw new InvalidArgumentException(
                sprintf(
                    'Customer parameter should be instance of %s or %s',
                    Customer::class,
                    PropertyPathInterface::class
                )
            );
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function isConditionAllowed($context)
    {
        $customer = $this->resolveValue($context, $this->customer);

        if ($customer instanceof Customer && $customer->getId()) {
            return $this->helper->hasAssignments($customer);
        }

        return false;
    }
}
