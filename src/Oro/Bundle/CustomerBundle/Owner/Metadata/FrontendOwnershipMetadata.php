<?php

namespace Oro\Bundle\CustomerBundle\Owner\Metadata;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;

/**
 * This class represents the entity ownership metadata for CustomerUser
 */
class FrontendOwnershipMetadata extends OwnershipMetadata
{
    const OWNER_TYPE_FRONTEND_USER = 4;
    const OWNER_TYPE_FRONTEND_CUSTOMER = 5;

    /** @var string */
    protected $customerFieldName;

    /** @var string */
    protected $customerColumnName;

    /**
     * {@inheritDoc}
     */
    public function __construct(
        $ownerType = '',
        $ownerFieldName = '',
        $ownerColumnName = '',
        $organizationFieldName = '',
        $organizationColumnName = '',
        $customerFieldName = '',
        $customerColumnName = ''
    ) {
        $this->customerFieldName = $customerFieldName;
        $this->customerColumnName = $customerColumnName;

        parent::__construct(
            $ownerType,
            $ownerFieldName,
            $ownerColumnName,
            $organizationFieldName,
            $organizationColumnName
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isUserOwned()
    {
        return self::OWNER_TYPE_FRONTEND_USER === $this->ownerType;
    }

    /**
     * {@inheritdoc}
     */
    public function isBusinessUnitOwned()
    {
        return self::OWNER_TYPE_FRONTEND_CUSTOMER === $this->ownerType;
    }

    /**
     * {@inheritdoc}
     */
    public function isOrganizationOwned()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessLevelNames()
    {
        if (!$this->hasOwner()) {
            return [
                AccessLevel::NONE_LEVEL => AccessLevel::NONE_LEVEL_NAME,
                AccessLevel::SYSTEM_LEVEL => AccessLevel::getAccessLevelName(AccessLevel::SYSTEM_LEVEL),
            ];
        }

        if ($this->isUserOwned()) {
            $maxLevel = AccessLevel::DEEP_LEVEL;
            $minLevel = AccessLevel::BASIC_LEVEL;
        } elseif ($this->isBusinessUnitOwned()) {
            $maxLevel = AccessLevel::DEEP_LEVEL;
            $minLevel = AccessLevel::LOCAL_LEVEL;
        } else {
            throw new \BadMethodCallException(sprintf('Owner type %s is not supported', $this->ownerType));
        }

        return AccessLevel::getAccessLevelNames($minLevel, $maxLevel);
    }

    /**
     * @return string|null
     */
    public function getCustomerFieldName()
    {
        return $this->customerFieldName;
    }

    /**
     * @return string|null
     */
    public function getCustomerColumnName()
    {
        return $this->customerColumnName;
    }

    public function __serialize(): array
    {
        return [
            $this->ownerType,
            $this->ownerFieldName,
            $this->ownerColumnName,
            $this->organizationFieldName,
            $this->organizationColumnName,
            $this->customerFieldName,
            $this->customerColumnName
        ];
    }

    public function __unserialize(array $serialized): void
    {
        [
            $this->ownerType,
            $this->ownerFieldName,
            $this->ownerColumnName,
            $this->organizationFieldName,
            $this->organizationColumnName,
            $this->customerFieldName,
            $this->customerColumnName
        ] = $serialized;
    }
}
