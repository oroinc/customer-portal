<?php

namespace Oro\Bundle\CustomerBundle\Owner\Metadata;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\AbstractOwnershipMetadataProvider;

/**
 * Provides metadata for entities with frontend ownership type
 */
class FrontendOwnershipMetadataProvider extends AbstractOwnershipMetadataProvider
{
    const ALIAS = 'frontend_ownership';

    /** @var EntityClassResolver */
    protected $entityClassResolver;

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var CacheProvider */
    private $cache;

    /** @var array */
    private $owningEntityNames;

    /** @var string */
    private $businessUnitClass;

    /** @var string */
    private $userClass;

    /**
     * @param array                  $owningEntityNames [owning entity type => entity class name, ...]
     * @param ConfigManager          $configManager
     * @param EntityClassResolver    $entityClassResolver
     * @param TokenAccessorInterface $tokenAccessor
     * @param CacheProvider          $cache
     */
    public function __construct(
        array $owningEntityNames,
        ConfigManager $configManager,
        EntityClassResolver $entityClassResolver,
        TokenAccessorInterface $tokenAccessor,
        CacheProvider $cache
    ) {
        parent::__construct($configManager);
        $this->owningEntityNames = $owningEntityNames;
        $this->entityClassResolver = $entityClassResolver;
        $this->tokenAccessor = $tokenAccessor;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserClass()
    {
        $this->ensureOwningEntityClassesInitialized();

        return $this->userClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getBusinessUnitClass()
    {
        $this->ensureOwningEntityClassesInitialized();

        return $this->businessUnitClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationClass()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supports()
    {
        return $this->tokenAccessor->getUser() instanceof CustomerUser
            || $this->tokenAccessor->getToken() instanceof AnonymousCustomerUserToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxAccessLevel($accessLevel, $className = null)
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

    /**
     * {@inheritdoc}
     */
    protected function getCache()
    {
        return $this->cache;
    }

    /**
     * {@inheritdoc}
     */
    protected function createNoOwnershipMetadata()
    {
        return new FrontendOwnershipMetadata();
    }

    /**
     * {@inheritdoc}
     */
    protected function getOwnershipMetadata(ConfigInterface $config)
    {
        $ownerType = $config->get('frontend_owner_type');
        $ownerFieldName = $config->get('frontend_owner_field_name');
        $ownerColumnName = $config->get('frontend_owner_column_name');
        $organizationFieldName = $config->get('organization_field_name');
        $organizationColumnName = $config->get('organization_column_name');
        $customerFieldName = $config->get('frontend_customer_field_name');
        $customerColumnName = $config->get('frontend_customer_column_name');

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

    /**
     * {@inheritdoc}
     */
    protected function getOwnershipConfigs()
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
    private function ensureOwningEntityClassesInitialized()
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
