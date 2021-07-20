<?php

namespace Oro\Bundle\WebsiteBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeFormData\CustomizeFormDataContext;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Assigns an entity to the current website.
 */
class SetWebsite implements ProcessorInterface
{
    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    /** @var WebsiteManager */
    private $websiteManager;

    /** @var string */
    private $websiteFieldName;

    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        WebsiteManager $websiteManager,
        string $websiteFieldName = 'website'
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->websiteManager = $websiteManager;
        $this->websiteFieldName = $websiteFieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var CustomizeFormDataContext $context */

        $websiteFormField = $context->findFormField($this->websiteFieldName);
        if (null === $websiteFormField
            || !$websiteFormField->isSubmitted()
            || !$websiteFormField->getConfig()->getMapped()
        ) {
            $this->setWebsite($context->getData());
        }
    }

    /**
     * Returns a website a processing entity should be assigned to.
     */
    private function getWebsite(): ?Website
    {
        return $this->websiteManager->getCurrentWebsite();
    }

    /**
     * Assigns the given entity to a website returned by getWebsite() method.
     * The entity's website property will not be changed if the getWebsite() method returns NULL
     * or the entity is already assigned to a website.
     *
     * @param object $entity
     */
    private function setWebsite($entity): void
    {
        $entityWebsite = $this->propertyAccessor->getValue($entity, $this->websiteFieldName);
        if (null === $entityWebsite) {
            $website = $this->getWebsite();
            if (null !== $website) {
                $this->propertyAccessor->setValue($entity, $this->websiteFieldName, $website);
            }
        }
    }
}
