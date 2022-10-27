<?php

namespace Oro\Bundle\WebsiteBundle\Acl\ORM\Walker;

use Oro\Bundle\CustomerBundle\Acl\ORM\Walker\FrontendAccessRuleWalkerContextFactory;
use Oro\Bundle\SecurityBundle\ORM\Walker\AccessRuleWalkerContext;
use Oro\Bundle\SecurityBundle\ORM\Walker\AccessRuleWalkerContextFactoryInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

/**
 * Adds the current storefront website ID to "websiteId" option of a created AccessRuleWalkerContext object.
 * The context is used to build a key for DQL query cache and as result it allows to avoid collisions
 * between queries for different websites.
 */
class WebsiteAccessRuleWalkerContextFactory implements AccessRuleWalkerContextFactoryInterface
{
    public const WEBSITE_ID = 'websiteId';

    /** @var AccessRuleWalkerContextFactoryInterface */
    private $innerFactory;

    /** @var WebsiteManager */
    private $websiteManager;

    public function __construct(
        AccessRuleWalkerContextFactoryInterface $innerFactory,
        WebsiteManager $websiteManager
    ) {
        $this->innerFactory = $innerFactory;
        $this->websiteManager = $websiteManager;
    }

    /**
     * {@inheritdoc}
     */
    public function createContext(string $permission): AccessRuleWalkerContext
    {
        $context = $this->innerFactory->createContext($permission);
        if ($context->getOption(FrontendAccessRuleWalkerContextFactory::FRONTEND, false)) {
            $website = $this->websiteManager->getCurrentWebsite();
            if (null !== $website) {
                $context->setOption(self::WEBSITE_ID, $website->getId());
            }
        }

        return $context;
    }
}
