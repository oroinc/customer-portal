<?php

namespace Oro\Bundle\CustomerBundle\Layout\DataProvider;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Handler\AbstractCustomerUserRoleHandler;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\UserBundle\Model\PrivilegeCategory;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCategoryProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provides ACL categories for storefront customer user role.
 */
class FrontendCustomerUserRoleTabOptionsProvider implements FrontendCustomerUserRoleOptionsProviderInterface
{
    private const DEFAULT_ACTION_CATEGORY = 'account_management';
    private const PRIVILEGE_TYPE          = 'entity';

    /** @var RolePrivilegeCategoryProvider */
    private $categoryProvider;

    /** @var TranslatorInterface */
    private $translator;

    /** @var AbstractCustomerUserRoleHandler */
    private $aclRoleHandler;

    /** @var PrivilegeCategory[]|null */
    private $options;

    public function __construct(
        RolePrivilegeCategoryProvider $categoryProvider,
        TranslatorInterface $translator,
        AbstractCustomerUserRoleHandler $aclRoleHandler
    ) {
        $this->categoryProvider = $categoryProvider;
        $this->translator = $translator;
        $this->aclRoleHandler = $aclRoleHandler;
    }

    public function getOptions(CustomerUserRole $role): array
    {
        if (null === $this->options) {
            $this->options = $this->initOptions($role);
        }

        return $this->formatOptions($this->options);
    }

    /**
     * @param PrivilegeCategory[] $options
     *
     * @return array
     */
    private function formatOptions(array $options): array
    {
        $data = [];
        foreach ($options as $option) {
            $data[] = [
                'id'    => $option->getId(),
                'label' => $this->translator->trans($option->getLabel()),
            ];
        }

        return ['data' => $data];
    }

    /**
     * @param CustomerUserRole $role
     *
     * @return PrivilegeCategory[]
     */
    private function initOptions(CustomerUserRole $role): array
    {
        $options = [];

        $privileges = $this->getPrivilegesForRole($role);
        $tabbedCategories = $this->getTabCategories();
        foreach ($privileges as $privilege) {
            $privilegeCategory = $privilege->getCategory();
            if (!isset($tabbedCategories[$privilegeCategory])) {
                $privilegeCategory = self::DEFAULT_ACTION_CATEGORY;
            }
            $category = $tabbedCategories[$privilegeCategory];
            $categoryId = $category->getId();
            if (!isset($options[$categoryId])) {
                $options[$categoryId] = $category;
            }
        }

        usort($options, static function (PrivilegeCategory $category1, PrivilegeCategory $category2) {
            return $category1->getPriority() <=> $category2->getPriority();
        });

        return $options;
    }

    /**
     * @return PrivilegeCategory[] [category id => category, ...]
     */
    private function getTabCategories(): array
    {
        $tabCategories = [];
        $categories = $this->categoryProvider->getCategories();
        foreach ($categories as $category) {
            if ($category->isTab()) {
                $tabCategories[$category->getId()] = $category;
            }
        }

        return $tabCategories;
    }

    /**
     * @param CustomerUserRole $role
     *
     * @return AclPrivilege[]
     */
    private function getPrivilegesForRole(CustomerUserRole $role): array
    {
        /** @var ArrayCollection[] $allPrivileges */
        $allPrivileges = $this->aclRoleHandler->getAllPrivileges($role);
        foreach ($allPrivileges as $type => $privileges) {
            if (self::PRIVILEGE_TYPE === $type) {
                return $privileges->toArray();
            }
        }

        return [];
    }
}
