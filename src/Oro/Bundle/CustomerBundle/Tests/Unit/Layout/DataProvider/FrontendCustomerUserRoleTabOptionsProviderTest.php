<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Layout\DataProvider;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Handler\AbstractCustomerUserRoleHandler;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserRoleTabOptionsProvider;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\UserBundle\Model\PrivilegeCategory;
use Oro\Bundle\UserBundle\Provider\PrivilegeCategoryProviderInterface;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCategoryProvider;
use Symfony\Component\Translation\TranslatorInterface;

class FrontendCustomerUserRoleTabOptionsProviderTest extends \PHPUnit_Framework_TestCase
{
    const CATEGORY1_ID = '1';
    const CATEGORY1_LABEL = 'Category 1';
    const CATEGORY1_PRIORITY = 0;

    const CATEGORY2_ID = '2';
    const CATEGORY2_LABEL = 'Category 2';
    const CATEGORY2_PRIORITY = 5;

    const CATEGORY_DEFAULT_ID = PrivilegeCategoryProviderInterface::DEFAULT_ACTION_CATEGORY;
    const CATEGORY_DEFAULT_LABEL = 'default';
    const CATEGORY_DEFAULT_PRIORITY = 10;

    /**
     * @var RolePrivilegeCategoryProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionCategoryProvider;

    /**
     * @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $translator;

    /**
     * @var AbstractCustomerUserRoleHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aclRoleHandler;

    /**
     * @var FrontendCustomerUserRoleTabOptionsProvider
     */
    private $provider;

    protected function setUp()
    {
        $this->permissionCategoryProvider = $this->createMock(RolePrivilegeCategoryProvider::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->aclRoleHandler = $this->createMock(AbstractCustomerUserRoleHandler::class);

        $this->provider = new FrontendCustomerUserRoleTabOptionsProvider(
            $this->permissionCategoryProvider,
            $this->translator,
            $this->aclRoleHandler
        );
    }

    public function testGetTabOptions()
    {
        $role = new CustomerUserRole();
        $privileges = [
            'other' => new ArrayCollection([
                $this->getPrivilegeWithCategory('4'),
            ]),
            'entity' => new ArrayCollection([
                new AclPrivilege(),
                $this->getPrivilegeWithCategory(self::CATEGORY1_ID),
                $this->getPrivilegeWithCategory(self::CATEGORY2_ID),
                $this->getPrivilegeWithCategory(self::CATEGORY2_ID),
                $this->getPrivilegeWithCategory('3'),
                new AclPrivilege(),
            ]),
        ];

        $tabCategories = [
            new PrivilegeCategory(self::CATEGORY1_ID, self::CATEGORY1_LABEL, true, self::CATEGORY1_PRIORITY),
            new PrivilegeCategory(self::CATEGORY2_ID, self::CATEGORY2_LABEL, true, self::CATEGORY2_PRIORITY),
            new PrivilegeCategory(
                self::CATEGORY_DEFAULT_ID,
                self::CATEGORY_DEFAULT_LABEL,
                true,
                self::CATEGORY_DEFAULT_PRIORITY
            ),
        ];

        $this->aclRoleHandler->expects(static::once())
            ->method('getAllPrivileges')
            ->with($role)
            ->willReturn($privileges);

        $this->permissionCategoryProvider->expects(static::once())
            ->method('getTabbedCategories')
            ->willReturn($tabCategories);

        $this->translator->expects(static::exactly(3))
            ->method('trans')
            ->withConsecutive([self::CATEGORY1_LABEL], [self::CATEGORY2_LABEL], [self::CATEGORY_DEFAULT_LABEL])
            ->willReturnOnConsecutiveCalls(self::CATEGORY1_LABEL, self::CATEGORY2_LABEL, self::CATEGORY_DEFAULT_LABEL);

        $expected = [
            'data' => [
                ['id' => self::CATEGORY1_ID, 'label' => self::CATEGORY1_LABEL],
                ['id' => self::CATEGORY2_ID, 'label' => self::CATEGORY2_LABEL],
                ['id' => self::CATEGORY_DEFAULT_ID, 'label' => self::CATEGORY_DEFAULT_LABEL],
            ],
        ];

        static::assertEquals($expected, $this->provider->getOptions($role));
    }

    /**
     * @param string $category
     *
     * @return AclPrivilege
     */
    private function getPrivilegeWithCategory($category)
    {
        $privilege = new AclPrivilege();
        $privilege->setCategory($category);

        return $privilege;
    }
}
