<?php

namespace Oro\Bundle\WebsiteBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Form\FormUtil;
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
    private PropertyAccessorInterface $propertyAccessor;
    private WebsiteManager $websiteManager;
    private string $websiteFieldName;

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
    public function process(ContextInterface $context): void
    {
        /** @var CustomizeFormDataContext $context */

        $websiteFormField = $context->findFormField($this->websiteFieldName);
        if (null === $websiteFormField
            || !$websiteFormField->isSubmitted()
            || !$websiteFormField->getConfig()->getMapped()
        ) {
            if ($this->setWebsite($context->getData())) {
                FormUtil::removeAccessGrantedValidationConstraint($context->getForm(), $this->websiteFieldName);
            }
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
     */
    private function setWebsite(object $entity): bool
    {
        $changed = false;
        $entityWebsite = $this->propertyAccessor->getValue($entity, $this->websiteFieldName);
        if (null === $entityWebsite) {
            $website = $this->getWebsite();
            if (null !== $website) {
                $this->propertyAccessor->setValue($entity, $this->websiteFieldName, $website);
                $changed = true;
            }
        }

        return $changed;
    }
}
