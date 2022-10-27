<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\CustomerBundle\Acl\Resolver\RoleTranslationPrefixResolver;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadataProvider;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Extension\ObjectIdentityHelper;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Owner\Metadata\ChainOwnershipMetadataProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The controller that provides Storefront ACL access levels for a specific domain object.
 */
class AclPermissionController
{
    /** @var EntityRoutingHelper */
    private $entityRoutingHelper;

    /** @var AclManager */
    private $aclManager;

    /** @var ChainOwnershipMetadataProvider */
    private $ownershipMetadataProvider;

    /** @var RoleTranslationPrefixResolver */
    private $roleTranslationPrefixResolver;

    public function __construct(
        EntityRoutingHelper $entityRoutingHelper,
        AclManager $aclManager,
        ChainOwnershipMetadataProvider $ownershipMetadataProvider,
        RoleTranslationPrefixResolver $roleTranslationPrefixResolver
    ) {
        $this->entityRoutingHelper = $entityRoutingHelper;
        $this->aclManager = $aclManager;
        $this->ownershipMetadataProvider = $ownershipMetadataProvider;
        $this->roleTranslationPrefixResolver = $roleTranslationPrefixResolver;
    }

    /**
     * @Route(
     *      "/acl-access-levels/{oid}/{permission}",
     *      name="oro_customer_acl_access_levels",
     *      requirements={"oid"="[\w]+:[\w\:\(\)\|]+", "permission"="[\w/]+"},
     *      defaults={"_format"="json", "permission"=null}
     * )
     * @Template
     */
    public function aclAccessLevelsAction(string $oid, string $permission = null): array
    {
        if (ObjectIdentityHelper::getExtensionKeyFromIdentityString($oid) === EntityAclExtension::NAME) {
            $oid = ObjectIdentityHelper::encodeIdentityString(
                EntityAclExtension::NAME,
                $this->entityRoutingHelper->resolveEntityClass(ObjectIdentityHelper::getClassFromIdentityString($oid))
            );
        }

        return [
            'levels'                => $this->getAccessLevels($oid, $permission),
            'roleTranslationPrefix' => $this->roleTranslationPrefixResolver->getPrefix()
        ];
    }

    private function getAccessLevels(string $oid, string $permission = null): array
    {
        $this->ownershipMetadataProvider->startProviderEmulation(FrontendOwnershipMetadataProvider::ALIAS);
        try {
            return $this->aclManager->getAccessLevels($oid, $permission);
        } finally {
            $this->ownershipMetadataProvider->stopProviderEmulation();
        }
    }
}
