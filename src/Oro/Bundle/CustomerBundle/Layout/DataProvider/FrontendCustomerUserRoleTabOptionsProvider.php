<?php

namespace Oro\Bundle\CustomerBundle\Layout\DataProvider;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Handler\AbstractCustomerUserRoleHandler;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\UserBundle\Model\PrivilegeCategory;
use Oro\Bundle\UserBundle\Provider\PrivilegeCategoryProviderInterface;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCategoryProvider;
use Symfony\Component\Translation\TranslatorInterface;

class FrontendCustomerUserRoleTabOptionsProvider implements FrontendCustomerUserRoleOptionsProviderInterface
{
    const PRIVILEGE_TYPE = 'entity';

    /**
     * @var RolePrivilegeCategoryProvider
     */
    private $permissionCategoryProvider;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var AbstractCustomerUserRoleHandler
     */
    private $aclRoleHandler;

    /**
     * @var PrivilegeCategory[]
     */
    private $options = [];

    /**
     * @param RolePrivilegeCategoryProvider   $permissionCategoryProvider
     * @param TranslatorInterface             $translator
     * @param AbstractCustomerUserRoleHandler $aclRoleHandler
     */
    public function __construct(
        RolePrivilegeCategoryProvider $permissionCategoryProvider,
        TranslatorInterface $translator,
        AbstractCustomerUserRoleHandler $aclRoleHandler
    ) {
        $this->permissionCategoryProvider = $permissionCategoryProvider;
        $this->translator = $translator;
        $this->aclRoleHandler = $aclRoleHandler;
    }

    /**
     * @param CustomerUserRole $role
     *
     * @return array
     */
    public function getOptions(CustomerUserRole $role)
    {
        if (empty($this->options)) {
            $this->initOptions($role);
        }

        return $this->formatOptions();
    }

    /**
     * @return array
     */
    private function formatOptions()
    {
        $result = ['data' => []];
        foreach ($this->options as $option) {
            $result['data'][] = [
                'id' => $option->getId(),
                'label' => $this->translator->trans($option->getLabel()),
            ];
        }

        return $result;
    }

    /**
     * @param CustomerUserRole $role
     */
    private function initOptions(CustomerUserRole $role)
    {
        $privileges = $this->getPrivilegesForRole($role);
        $tabbedCategories = $this->getTabCategories();

        foreach ($privileges as $privilege) {
            $privilegeCategory = $privilege->getCategory();
            if (!array_key_exists($privilegeCategory, $tabbedCategories)) {
                $privilegeCategory = PrivilegeCategoryProviderInterface::DEFAULT_ACTION_CATEGORY;
            }

            $this->addOption($tabbedCategories[$privilegeCategory]);
        }

        usort($this->options, function (PrivilegeCategory $category1, PrivilegeCategory $category2) {
            return $category1->getPriority() > $category2->getPriority();
        });
    }

    /**
     * @param PrivilegeCategory $category
     */
    private function addOption(PrivilegeCategory $category)
    {
        $categoryId = $category->getId();
        if (!array_key_exists($categoryId, $this->options)) {
            $this->options[$categoryId] = $category;
        }
    }

    /**
     * @return array<id => PrivilegeCategory>
     */
    private function getTabCategories()
    {
        $categories = [];
        foreach ($this->permissionCategoryProvider->getTabbedCategories() as $category) {
            $categories[$category->getId()] = $category;
        }

        return $categories;
    }

    /**
     * @param CustomerUserRole $role
     *
     * @return AclPrivilege[]
     */
    private function getPrivilegesForRole(CustomerUserRole $role)
    {
        /**
         * @var string          $type
         * @var ArrayCollection $privileges
         */
        foreach ($this->aclRoleHandler->getAllPrivileges($role) as $type => $privileges) {
            if ($type === self::PRIVILEGE_TYPE) {
                return $privileges->toArray();
            }
        }

        return [];
    }
}
