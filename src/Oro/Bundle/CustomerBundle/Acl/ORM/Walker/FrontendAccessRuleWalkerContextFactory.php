<?php

namespace Oro\Bundle\CustomerBundle\Acl\ORM\Walker;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AccessRuleWalkerContext;
use Oro\Bundle\SecurityBundle\ORM\Walker\AccessRuleWalkerContextFactoryInterface;

/**
 * Adds "frontend" option to a created AccessRuleWalkerContext object
 * when the current request is the storefront one.
 * The context is used to build a key for DQL query cache and as result it allows to avoid collisions
 * between storefront and management console queries.
 */
class FrontendAccessRuleWalkerContextFactory implements AccessRuleWalkerContextFactoryInterface
{
    public const FRONTEND = 'frontend';

    /** @var AccessRuleWalkerContextFactoryInterface */
    private $innerFactory;

    /** @var FrontendHelper */
    private $frontendHelper;

    public function __construct(
        AccessRuleWalkerContextFactoryInterface $innerFactory,
        FrontendHelper $frontendHelper
    ) {
        $this->innerFactory = $innerFactory;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function createContext(string $permission): AccessRuleWalkerContext
    {
        $context = $this->innerFactory->createContext($permission);
        if ($this->frontendHelper->isFrontendRequest()) {
            $context->setOption(self::FRONTEND, true);
        }

        return $context;
    }
}
