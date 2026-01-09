<?php

namespace Oro\Bundle\WebsiteBundle\Model\Action;

use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\ConfigExpression\ContextAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * Handles the action of assigning the current website to a context attribute.
 *
 * This action retrieves the currently active website from the {@see WebsiteManager} and assigns it
 * to a specified attribute in the action context. It is typically used in workflow definitions
 * and action chains to make the current website available for subsequent operations.
 */
class AssignCurrentWebsite extends AbstractAction
{
    /**
     * @var PropertyPathInterface
     */
    protected $attribute;

    /**
     * @var WebsiteManager
     */
    protected $websiteManager;

    public function __construct(ContextAccessor $contextAccessor, WebsiteManager $websiteManager)
    {
        parent::__construct($contextAccessor);

        $this->websiteManager = $websiteManager;
    }

    #[\Override]
    protected function executeAction($context)
    {
        $this->contextAccessor->setValue($context, $this->attribute, $this->websiteManager->getCurrentWebsite());
    }

    #[\Override]
    public function initialize(array $options)
    {
        if (count($options) !== 1) {
            throw new InvalidParameterException('Only one attribute parameter must be defined');
        }

        $attribute = null;
        if (array_key_exists(0, $options)) {
            $attribute = $options[0];
        } elseif (array_key_exists('attribute', $options)) {
            $attribute = $options['attribute'];
        }

        if (!$attribute) {
            throw new InvalidParameterException('Attribute must be defined');
        }
        if (!$attribute instanceof PropertyPathInterface) {
            throw new InvalidParameterException('Attribute must be valid property definition');
        }

        $this->attribute = $attribute;

        return $this;
    }
}
