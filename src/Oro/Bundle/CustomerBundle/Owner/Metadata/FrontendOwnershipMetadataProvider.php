<?php

namespace Oro\Bundle\CustomerBundle\Owner\Metadata;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\AbstractOwnershipMetadataProvider;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Provides metadata for entities with frontend ownership type
 */
class FrontendOwnershipMetadataProvider extends AbstractOwnershipMetadataProvider
{
    const ALIAS = 'frontend_ownership';

    protected EntityClassResolver $entityClassResolver;
    protected TokenAccessorInterface $tokenAccessor;
    private CacheInterface $cache;
    private ?array $owningEntityNames;
    private string $businessUnitClass;
    private string $userClass;

    /**
     * @param array                  $owningEntityNames [owning entity type => entity class name, ...]
     * @param ConfigManager          $configManager
     * @param EntityClassResolver    $entityClassResolver
     * @param TokenAccessorInterface $tokenAccessor
     * @param CacheInterface          $cache
     */
    public function __construct(
        array $owningEntityNames,
        ConfigManager $configManager,
        EntityClassResolver $entityClassResolver,
        TokenAccessorInterface $tokenAccessor,
        CacheInterface $cache
    ) {
        parent::__construct($configManager);
        $this->owningEntityNames = $owningEntityNames;
        $this->entityClassResolver = $entityClassResolver;
        $this->tokenAccessor = $tokenAccessor;
        $this->cache = $cache;
    }

    public function getUserClass(): string
    {
        $this->ensureOwningEntityClassesInitialized();

        return $this->userClass;
    }

    public function getBusinessUnitClass(): string
    {
        $this->ensureOwningEntityClassesInitialized();

        return $this->businessUnitClass;
    }

    public function getOrganizationClass(): ?string
    {
        return null;
    }

    public function supports(): bool
    {
        return $this->tokenAccessor->getUser() instanceof CustomerUser
            || $this->tokenAccessor->getToken() instanceof AnonymousCustomerUserToken;
    }

    public function getMaxAccessLevel($accessLevel, $className = null): int
    {
        $maxLevel = $accessLevel;
        if ($accessLevel > AccessLevel::DEEP_LEVEL) {
            if ($className) {
                $metadata = $this->getMetadata($className);
                if ($metadata->hasOwner()) {
                    $maxLevel = AccessLevel::DEEP_LEVEL;
                }
            } else {
                $maxLevel = AccessLevel::DEEP_LEVEL;
            }
        }

        return $maxLevel;
    }

    protected function getCache(): CacheInterface
    {
        return $this->cache;
    }

    protected function createNoOwnershipMetadata(): FrontendOwnershipMetadata
    {
        return new FrontendOwnershipMetadata();
    }

    protected function getOwnershipMetadata(ConfigInterface $config): FrontendOwnershipMetadata
    {
        $ownerType = $config->get('frontend_owner_type');
        $ownerFieldName = $config->get('frontend_owner_field_name');
        $ownerColumnName = $config->get('frontend_owner_column_name');
        $organizationFieldName = $config->get('organization_field_name');
        $organizationColumnName = $config->get('organization_column_name');
        $customerFieldName = $config->get('frontend_customer_field_name');
        $customerColumnName = $config->get('frontend_customer_column_name');

        if (!$ownerType) {
            $ownerType = '';
        }

        return new FrontendOwnershipMetadata(
            $ownerType,
            $ownerFieldName,
            $ownerColumnName,
            $organizationFieldName,
            $organizationColumnName,
            $customerFieldName,
            $customerColumnName
        );
    }

    protected function getOwnershipConfigs(): array
    {
        // only commerce entities can have frontend ownership
        $configs = parent::getOwnershipConfigs();
        foreach ($configs as $key => $value) {
            $className = $value->getId()->getClassName();
            if ($this->configManager->hasConfig($className)) {
                $securityConfig = $this->configManager->getEntityConfig('security', $className);
                if ($securityConfig->get('group_name') === CustomerUser::SECURITY_GROUP) {
                    continue;
                }
            }

            unset($configs[$key]);
        }

        return $configs;
    }

    /**
     * Makes sure that the owning entity classes are initialized.
     */
    private function ensureOwningEntityClassesInitialized(): void
    {
        if (null === $this->owningEntityNames) {
            // already initialized
            return;
        }

        if (!isset(
            $this->owningEntityNames['business_unit'],
            $this->owningEntityNames['user']
        )) {
            throw new \InvalidArgumentException(
                'The $owningEntityNames must contains "business_unit" and "user" keys.'
            );
        }

        $this->businessUnitClass = $this->entityClassResolver->getEntityClass(
            $this->owningEntityNames['business_unit']
        );
        $this->userClass = $this->entityClassResolver->getEntityClass(
            $this->owningEntityNames['user']
        );

        // remove source data to mark that the initialization passed
        $this->owningEntityNames = null;
    }
}
