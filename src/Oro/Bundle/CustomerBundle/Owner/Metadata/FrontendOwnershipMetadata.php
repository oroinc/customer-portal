<?php

namespace Oro\Bundle\CustomerBundle\Owner\Metadata;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;

/**
 * This class represents the storefront entity ownership metadata.
 * Supported owner types: "NONE", "FRONTEND_CUSTOMER" or "FRONTEND_USER".
 */
class FrontendOwnershipMetadata extends OwnershipMetadata
{
    public const OWNER_TYPE_FRONTEND_USER = 4;
    public const OWNER_TYPE_FRONTEND_CUSTOMER = 5;

    protected string $customerFieldName;
    protected string $customerColumnName;

    public function __construct(
        string $ownerType = '',
        string $ownerFieldName = '',
        string $ownerColumnName = '',
        string $organizationFieldName = '',
        string $organizationColumnName = '',
        string $customerFieldName = '',
        string $customerColumnName = ''
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

    #[\Override]
    public function isUserOwned(): bool
    {
        return self::OWNER_TYPE_FRONTEND_USER === $this->ownerType;
    }

    #[\Override]
    public function isBusinessUnitOwned(): bool
    {
        return self::OWNER_TYPE_FRONTEND_CUSTOMER === $this->ownerType;
    }

    #[\Override]
    public function isOrganizationOwned(): bool
    {
        return false;
    }

    #[\Override]
    public function getAccessLevelNames(): array
    {
        if (!$this->hasOwner()) {
            return [
                AccessLevel::NONE_LEVEL => AccessLevel::NONE_LEVEL_NAME,
                AccessLevel::SYSTEM_LEVEL => AccessLevel::getAccessLevelName(AccessLevel::SYSTEM_LEVEL),
            ];
        }

        return AccessLevel::getAccessLevelNames(
            $this->isUserOwned() ? AccessLevel::BASIC_LEVEL : AccessLevel::LOCAL_LEVEL,
            AccessLevel::DEEP_LEVEL
        );
    }

    public function getCustomerFieldName(): string
    {
        return $this->customerFieldName;
    }

    public function getCustomerColumnName(): string
    {
        return $this->customerColumnName;
    }

    #[\Override]
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

    #[\Override]
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

    #[\Override]
    protected function resolveOwnerType(string $ownerType): int
    {
        $resolvedOwnerType = parent::resolveOwnerType($ownerType);
        if (self::OWNER_TYPE_NONE !== $resolvedOwnerType
            && self::OWNER_TYPE_FRONTEND_CUSTOMER !== $resolvedOwnerType
            && self::OWNER_TYPE_FRONTEND_USER !== $resolvedOwnerType
        ) {
            throw new \InvalidArgumentException(sprintf('Unsupported owner type: %s.', $ownerType));
        }

        return $resolvedOwnerType;
    }

    #[\Override]
    protected function initialize(): void
    {
    }
}
