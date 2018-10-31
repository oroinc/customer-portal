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
     * @param string $customerFieldName
     */
    public function setCustomerFieldName($customerFieldName)
    {
        $this->customerFieldName = $customerFieldName;
    }

    /**
     * @param string $customerColumnName
     */
    public function setCustomerColumnName($customerColumnName)
    {
        $this->customerColumnName = $customerColumnName;
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

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(
            [
                $this->ownerType,
                $this->ownerFieldName,
                $this->ownerColumnName,
                $this->organizationFieldName,
                $this->organizationColumnName,
                $this->customerFieldName,
                $this->customerColumnName
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->ownerType,
            $this->ownerFieldName,
            $this->ownerColumnName,
            $this->organizationFieldName,
            $this->organizationColumnName,
            $this->customerFieldName,
            $this->customerColumnName
        ) = unserialize($serialized);
    }
}
