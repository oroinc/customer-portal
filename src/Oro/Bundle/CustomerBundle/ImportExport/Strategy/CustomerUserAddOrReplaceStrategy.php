<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Strategy;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueCustomerUserNameAndEmail;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

/**
 * Add or replace import strategy for CustomerUser entity.
 * Handles existing entity search by case insensitive email policy in accordance with system configuration.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class CustomerUserAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    public const PROCESSED_EMAILS = 'customer_user_processed_emails';

    /** @var ConfigManager */
    private $configManager;

    /** @var bool|null */
    private $isCaseSensitiveEmailEnabled;

    public function setConfigManager(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function afterProcessEntity($entity)
    {
        $entity = parent::afterProcessEntity($entity);
        $entity = $this->verifyIfUserIsGrantedToUpdateOwner($entity);

        return $entity;
    }

    /**
     * Add frontendOwner to addresses search context to prevent same addresses "stealing" by another customer user.
     *
     * {@inheritdoc}
     */
    protected function generateSearchContextForRelationsUpdate($entity, $entityName, $fieldName, $isPersistRelation)
    {
        $context = parent::generateSearchContextForRelationsUpdate(
            $entity,
            $entityName,
            $fieldName,
            $isPersistRelation
        );

        if ($fieldName === 'addresses') {
            return array_merge($context, ['frontendOwner' => $entity]);
        }

        return $context;
    }

    /**
     * {@inheritdoc}
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        if ($entity instanceof CustomerUser && $entity->isGuest()) {
            $searchContext = [$entity];
        }

        return parent::findExistingEntity($entity, $searchContext);
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntityByIdentityValues($entityName, array $identityValues)
    {
        if (is_a($entityName, CustomerUserRole::class, true)) {
            $entity = $this->findCustomerUserRoleByRoleWithUniqueSuffix($entityName, $identityValues);
            if ($entity) {
                return $entity;
            }
        }

        return parent::findEntityByIdentityValues(
            $entityName,
            $this->handleCaseInsensitiveEmail($entityName, $identityValues)
        );
    }

    /**
     * This is a workaround to prevent the use of reflection to set 'role' field of the CustomerUserRole entity.
     * It's necessary because we use the 'CustomerUserRole::setRole()' method during the entity denormalization
     * which always calls the 'strtoupper()' function for the given value.
     *
     * @param string $entityName
     * @param array $identityValues
     * @return object|null
     */
    private function findCustomerUserRoleByRoleWithUniqueSuffix($entityName, array $identityValues): ?object
    {
        $role = $identityValues['role'] ?? null;
        if (!$role) {
            return null;
        }

        $position = strrpos($role, '_');

        $identityValues['role'] = substr($role, 0, $position) . strtolower(substr($role, $position));

        return parent::findEntityByIdentityValues($entityName, $identityValues);
    }

    /**
     * {@inheritdoc}
     */
    protected function combineIdentityValues($entity, $entityClass, array $searchContext)
    {
        return $this->handleCaseInsensitiveEmail(
            $entityClass,
            parent::combineIdentityValues($entity, $entityClass, $searchContext)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function importExistingEntity(
        $entity,
        $existingEntity,
        $itemData = null,
        array $excludedFields = []
    ) {
        $entitiesOfCustomerUser = $entity instanceof CustomerUser && $existingEntity instanceof CustomerUser;

        if ($itemData !== null && $entitiesOfCustomerUser) {
            foreach ($itemData as $fieldName => $fieldValue) {
                if ($fieldValue === null || (is_array($fieldValue) && count($fieldValue) === 0)) {
                    $excludedFields[] = $fieldName;
                }
            }
        }

        parent::importExistingEntity($entity, $existingEntity, $itemData, $excludedFields);
    }

    /**
     * @param CustomerUser $entity
     * {@inheritdoc}
     */
    protected function validateAndUpdateContext($entity)
    {
        $validationErrors = $this->strategyHelper->validateEntity($entity) ?? [];
        $validationErrors = array_merge($validationErrors, $this->validateEmailsInCurrentBatch($entity));
        if ($validationErrors) {
            $this->processValidationErrors($entity, $validationErrors);

            return null;
        }

        $this->updateContextCounters($entity);

        return $entity;
    }

    /**
     * @param $entity
     * @return CustomerUser|null
     */
    private function verifyIfUserIsGrantedToUpdateOwner(CustomerUser $entity = null)
    {
        if ($entity === null) {
            return null;
        }

        if ($this->isNewCustomerUser($entity)) {
            return $this->handleOwnerOfNewCustomerUser($entity);
        }

        return $this->handleOwnerOfExistingCustomerUser($entity);
    }

    /**
     * @param CustomerUser $entity
     * @return bool
     */
    private function isNewCustomerUser(CustomerUser $entity)
    {
        return $entity->getId() === null;
    }

    /**
     * @param CustomerUser $entity
     * @return array
     */
    private function getOriginalEntityData(CustomerUser $entity)
    {
        return $this->doctrineHelper->getEntityManagerForClass(CustomerUser::class)
            ->getUnitOfWork()->getOriginalEntityData($entity);
    }

    /**
     * @param CustomerUser $entity
     * @return null|CustomerUser
     */
    private function handleOwnerOfNewCustomerUser(CustomerUser $entity)
    {
        if (null === $this->strategyHelper->getLoggedUser()) {
            return $entity;
        }

        $entityOwner = $entity->getOwner();

        if ($entityOwner === null) {
            $entity->setOwner($this->strategyHelper->getLoggedUser());

            return $entity;
        }

        if ($entityOwner === $this->strategyHelper->getLoggedUser()) {
            return $entity;
        }

        if ($this->strategyHelper->checkEntityOwnerPermissions($this->context, $entity, true)) {
            return $entity;
        }

        return null;
    }

    /**
     * @param CustomerUser $entity
     * @return null|CustomerUser
     */
    private function handleOwnerOfExistingCustomerUser(CustomerUser $entity)
    {
        /** @var CustomerUser $originalEntity */
        $originalEntity = $this->getOriginalEntityData($entity);

        $owner = $entity->getOwner();
        $ownerChanged = $owner !== null && $originalEntity['owner'] !== $owner;
        $userIsNotGrantedToAssign = !$this->strategyHelper->checkEntityOwnerPermissions($this->context, $entity, true);

        if ($ownerChanged && $userIsNotGrantedToAssign) {
            //User tries to change owner, but has no right to change owner of CustomerUser
            //or has no access to provided user.
            return null;
        }

        return $entity;
    }

    /**
     * @param string $entityClass
     * @param array|null $identityValues
     * @return array|null
     */
    private function handleCaseInsensitiveEmail(string $entityClass, $identityValues): ?array
    {
        if (is_a($entityClass, CustomerUser::class, true) &&
            $this->isCaseInsensitiveEmailEnabled() &&
            isset($identityValues['email'])
        ) {
            $identityValues['emailLowercase'] = mb_strtolower($identityValues['email']);
            unset($identityValues['email']);
        }

        return $identityValues;
    }

    private function isCaseInsensitiveEmailEnabled(): bool
    {
        if ($this->isCaseSensitiveEmailEnabled === null) {
            $this->isCaseSensitiveEmailEnabled = false;

            if ($this->configManager) {
                $this->isCaseSensitiveEmailEnabled = (bool) $this->configManager
                    ->get('oro_customer.case_insensitive_email_addresses_enabled');
            }
        }

        return $this->isCaseSensitiveEmailEnabled;
    }

    /**
     * Remember and check emails processed in a current batch to guarantee uniqueness.
     *
     * @param CustomerUser $entity
     * @return array|string[]
     */
    private function validateEmailsInCurrentBatch(CustomerUser $entity): array
    {
        $processedEntities = (array)$this->context->getValue(self::PROCESSED_EMAILS);
        $email = $this->getEntityEmail($entity);

        // Rise an error, if the email already processed for existing entity.
        // Do not rise an error if same email in batch used by several new records
        if (array_key_exists($email, $processedEntities)
            && ($entity->getId() || $processedEntities[$email])
            && $processedEntities[$email] !== $entity->getId()
        ) {
            $uniqueConstraint = new UniqueCustomerUserNameAndEmail();

            return [
                $this->translator->trans(
                    $uniqueConstraint->message,
                    [],
                    'validators'
                )
            ];
        }

        $processedEntities[$email] = $entity->getId();
        $this->context->setValue(self::PROCESSED_EMAILS, $processedEntities);

        return [];
    }

    private function getEntityEmail(CustomerUser $entity): ?string
    {
        if ($this->isCaseInsensitiveEmailEnabled()) {
            return $entity->getEmailLowercase();
        }

        return $entity->getEmail();
    }
}
