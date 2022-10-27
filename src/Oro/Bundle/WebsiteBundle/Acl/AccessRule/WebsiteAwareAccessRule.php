<?php

namespace Oro\Bundle\WebsiteBundle\Acl\AccessRule;

use Oro\Bundle\SecurityBundle\AccessRule\AccessRuleInterface;
use Oro\Bundle\SecurityBundle\AccessRule\Criteria;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Comparison;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Path;
use Oro\Bundle\WebsiteBundle\Acl\ORM\Walker\WebsiteAccessRuleWalkerContextFactory;

/**
 * Denies access to entities that does not belong to the current website.
 */
class WebsiteAwareAccessRule implements AccessRuleInterface
{
    /** @var string */
    private $websiteFieldName;

    public function __construct(string $websiteFieldName = 'website')
    {
        $this->websiteFieldName = $websiteFieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(Criteria $criteria): bool
    {
        return $criteria->hasOption(WebsiteAccessRuleWalkerContextFactory::WEBSITE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function process(Criteria $criteria): void
    {
        $criteria->andExpression(
            new Comparison(
                new Path($this->websiteFieldName, $criteria->getAlias()),
                Comparison::EQ,
                $criteria->getOption(WebsiteAccessRuleWalkerContextFactory::WEBSITE_ID)
            )
        );
    }
}
