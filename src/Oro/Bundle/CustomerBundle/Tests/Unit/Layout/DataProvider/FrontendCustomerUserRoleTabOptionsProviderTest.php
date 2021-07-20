<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Layout\DataProvider;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Handler\AbstractCustomerUserRoleHandler;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserRoleTabOptionsProvider;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\UserBundle\Model\PrivilegeCategory;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCategoryProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

class FrontendCustomerUserRoleTabOptionsProviderTest extends \PHPUnit\Framework\TestCase
{
    private const CATEGORY1_ID       = '1';
    private const CATEGORY1_LABEL    = 'Category 1';
    private const CATEGORY1_PRIORITY = 0;

    private const CATEGORY2_ID       = '2';
    private const CATEGORY2_LABEL    = 'Category 2';
    private const CATEGORY2_PRIORITY = 5;

    private const DEFAULT_CATEGORY_ID       = 'account_management';
    private const DEFAULT_CATEGORY_LABEL    = 'default';
    private const DEFAULT_CATEGORY_PRIORITY = 10;

    /** @var RolePrivilegeCategoryProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $categoryProvider;

    /** @var AbstractCustomerUserRoleHandler|\PHPUnit\Framework\MockObject\MockObject */
    private $aclRoleHandler;

    /** @var FrontendCustomerUserRoleTabOptionsProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->categoryProvider = $this->createMock(RolePrivilegeCategoryProvider::class);
        $this->aclRoleHandler = $this->createMock(AbstractCustomerUserRoleHandler::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::any())
            ->method('trans')
            ->willReturnCallback(function ($value) {
                return 'translated_' . $value;
            });

        $this->provider = new FrontendCustomerUserRoleTabOptionsProvider(
            $this->categoryProvider,
            $translator,
            $this->aclRoleHandler
        );
    }

    private function getPrivilegeWithCategory(string $category): AclPrivilege
    {
        $privilege = new AclPrivilege();
        $privilege->setCategory($category);

        return $privilege;
    }

    public function testGetTabOptions()
    {
        $role = new CustomerUserRole('');
        $privileges = [
            'other'  => new ArrayCollection([
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

        $categories = [
            new PrivilegeCategory('3', 'Category 3', false, 0),
            new PrivilegeCategory(self::CATEGORY1_ID, self::CATEGORY1_LABEL, true, self::CATEGORY1_PRIORITY),
            new PrivilegeCategory(self::CATEGORY2_ID, self::CATEGORY2_LABEL, true, self::CATEGORY2_PRIORITY),
            new PrivilegeCategory(
                self::DEFAULT_CATEGORY_ID,
                self::DEFAULT_CATEGORY_LABEL,
                true,
                self::DEFAULT_CATEGORY_PRIORITY
            ),
        ];

        $this->aclRoleHandler->expects(self::once())
            ->method('getAllPrivileges')
            ->with($role)
            ->willReturn($privileges);

        $this->categoryProvider->expects(self::once())
            ->method('getCategories')
            ->willReturn($categories);

        $expected = [
            'data' => [
                ['id' => self::CATEGORY1_ID, 'label' => 'translated_' . self::CATEGORY1_LABEL],
                ['id' => self::CATEGORY2_ID, 'label' => 'translated_' . self::CATEGORY2_LABEL],
                ['id' => self::DEFAULT_CATEGORY_ID, 'label' => 'translated_' . self::DEFAULT_CATEGORY_LABEL]
            ]
        ];

        self::assertEquals($expected, $this->provider->getOptions($role));
        // test local cache
        self::assertEquals($expected, $this->provider->getOptions($role));
    }
}
