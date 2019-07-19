<?php

namespace Oro\Bundle\WebsiteBundle\ORM\Walker;

use Oro\Bundle\SecurityBundle\AccessRule\AccessRuleExecutor;
use Oro\Bundle\SecurityBundle\ORM\Walker\AccessRuleWalker;
use Oro\Bundle\SecurityBundle\ORM\Walker\AccessRuleWalkerContext;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper as BaseAclHelper;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * This class modifies AccessRuleWalkerContext and set Organization from current website on storefront.
 */
class AclHelper extends BaseAclHelper
{
    /** @var WebsiteManager */
    private $websiteManager;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param AccessRuleExecutor $accessRuleExecutor
     * @param WebsiteManager $websiteManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AccessRuleExecutor $accessRuleExecutor,
        WebsiteManager $websiteManager
    ) {
        parent::__construct($tokenStorage, $accessRuleExecutor);

        $this->websiteManager = $websiteManager;
    }

    /**
     * {@inheritdoc}
     */
    public function apply($query, string $permission = 'VIEW', array $options = [])
    {
        $query = parent::apply($query, $permission, $options);

        $website = $this->websiteManager->getCurrentWebsite();
        if ($website) {
            /** @var AccessRuleWalkerContext $innerContext */
            $innerContext = $query->getHint(AccessRuleWalker::CONTEXT);

            $context = new AccessRuleWalkerContext(
                $this->accessRuleExecutor,
                $innerContext->getPermission(),
                $innerContext->getUserClass(),
                $innerContext->getUserId(),
                $website->getOrganization()->getId()
            );

            foreach ($innerContext->getOptions() as $key => $value) {
                $context->setOption($key, $value);
            }

            $query->setHint(AccessRuleWalker::CONTEXT, $context);
        }

        return $query;
    }
}
