<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\EntityBundle\Provider\EntityNameProvider;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;

/**
 * Customer name should always be its short name instead of concatenating all string fields by default
 */
class CustomerEntityNameProvider implements EntityNameProviderInterface
{
    /** @var EntityNameProvider */
    private $defaultEntityNameProvider;

    public function __construct(EntityNameProvider $defaultEntityNameProvider)
    {
        $this->defaultEntityNameProvider = $defaultEntityNameProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function getName($format, $locale, $entity)
    {
        if (!is_a($entity, Customer::class)) {
            return false;
        }

        return $this->defaultEntityNameProvider->getName(
            EntityNameProviderInterface::SHORT,
            $locale,
            $entity
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getNameDQL($format, $locale, $className, $alias)
    {
        if (!is_a($className, Customer::class, true)) {
            return false;
        }

        return $this->defaultEntityNameProvider->getNameDQL(
            EntityNameProviderInterface::SHORT,
            $locale,
            $className,
            $alias
        );
    }
}
