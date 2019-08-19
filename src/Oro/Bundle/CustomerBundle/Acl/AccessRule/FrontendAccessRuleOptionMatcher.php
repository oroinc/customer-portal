<?php

namespace Oro\Bundle\CustomerBundle\Acl\AccessRule;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\SecurityBundle\AccessRule\AccessRuleOptionMatcherInterface;
use Oro\Bundle\SecurityBundle\AccessRule\Criteria;

/**
 * A class that checks whether access rules are applicable for a criteria object.
 * The access rule option matcher that supports the following options:
 * * frontend - whether the current security context represents a storefront request
 */
class FrontendAccessRuleOptionMatcher implements AccessRuleOptionMatcherInterface
{
    private const OPTION_FRONTEND = 'frontend';

    /** @var AccessRuleOptionMatcherInterface */
    private $innerMatcher;

    /** @var FrontendHelper */
    private $frontendHelper;

    /**
     * @param AccessRuleOptionMatcherInterface $innerMatcher
     * @param FrontendHelper                   $frontendHelper
     */
    public function __construct(
        AccessRuleOptionMatcherInterface $innerMatcher,
        FrontendHelper $frontendHelper
    ) {
        $this->innerMatcher = $innerMatcher;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function matches(Criteria $criteria, string $optionName, $optionValue): bool
    {
        if (self::OPTION_FRONTEND === $optionName) {
            $isFrontend = $this->frontendHelper->isFrontendRequest();

            return
                (true === $optionValue && $isFrontend)
                || (false === $optionValue && !$isFrontend);
        }

        return $this->innerMatcher->matches($criteria, $optionName, $optionValue);
    }
}
