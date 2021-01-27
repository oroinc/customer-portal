<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRoleSelectType;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendCustomerUserRoleSelectTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    /**
     * @var FrontendCustomerUserRoleSelectType
     */
    protected $formType;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $tokenAccessor;

    /** @var $registry Registry|\PHPUnit\Framework\MockObject\MockObject */
    protected $registry;

    /** @var QueryBuilder */
    protected $qb;

    protected function setUp(): void
    {
        $customer = $this->createCustomer(1, 'customer');
        $organization = $this->createOrganization(1);
        $user = new CustomerUser();

        $user->setCustomer($customer);
        $user->setOrganization($organization);
        $this->qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->tokenAccessor->expects($this->any())->method('getUser')->willReturn($user);
        $this->registry = $this->createMock(ManagerRegistry::class);
        /** @var $repo CustomerUserRoleRepository|\PHPUnit\Framework\MockObject\MockObject */
        $repo = $this->getMockBuilder('Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->any())
            ->method('createQueryBuilder')
            ->with('customer')
            ->willReturn($this->qb);
        /** @var $em ObjectManager|\PHPUnit\Framework\MockObject\MockObject */
        $em = $this->createMock('Doctrine\Persistence\ObjectManager');
        $em->expects($this->any())
            ->method('getRepository')
            ->with('Oro\Bundle\CustomerBundle\Entity\CustomerUserRole')
            ->willReturn($repo);
        $this->registry->expects($this->any())->method('getManagerForClass')->willReturn($em);
        $this->formType = new FrontendCustomerUserRoleSelectType(
            $this->tokenAccessor,
            $this->registry
        );
        $this->formType->setRoleClass('Oro\Bundle\CustomerBundle\Entity\CustomerUserRole');

        parent::setUp();
    }

    public function testGetRegistry()
    {
        $this->assertEquals($this->formType->getRegistry(), $this->registry);
    }

    public function testGetParent()
    {
        $this->assertEquals(CustomerUserRoleSelectType::class, $this->formType->getParent());
    }

    public function testConfigureOptions()
    {
        /** @var $resolver OptionsResolver|\PHPUnit\Framework\MockObject\MockObject */
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');

        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'))
            ->willReturnCallback(
                function (array $options) {
                    $this->assertArrayHasKey('query_builder', $options);
                    $this->assertInstanceOf('\Closure', $options['query_builder']);
                    $this->assertArrayHasKey('acl_options', $options);
                    $this->assertEquals(
                        [
                            'permission' => 'VIEW'
                        ],
                        $options['acl_options']
                    );
                }
            );
        $this->formType->configureOptions($resolver);
    }

    public function testEmptyUser()
    {
        /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject $tokenAccessor */
        $tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $tokenAccessor->expects($this->once())->method('getUser')->willReturn(null);
        /** @var $resolver OptionsResolver|\PHPUnit\Framework\MockObject\MockObject */
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $roleFormType = new FrontendCustomerUserRoleSelectType($tokenAccessor, $this->registry);
        $roleFormType->configureOptions($resolver);
    }

    /**
     * @param int $id
     * @param string $name
     * @return Customer
     */
    protected function createCustomer($id, $name)
    {
        $customer = $this->getEntity('Oro\Bundle\CustomerBundle\Entity\Customer', ['id' => $id]);
        $customer->setName($name);

        return $customer;
    }

    /**
     * @param int $id
     * @return Customer
     */
    protected function createOrganization($id)
    {
        return $this->getEntity('Oro\Bundle\OrganizationBundle\Entity\Organization', ['id' => $id]);
    }

    /**
     * @return CustomerUserRole[]
     */
    protected function getRoles()
    {
        return [
            1 => $this->getRole(1, 'test01'),
            2 => $this->getRole(2, 'test02'),
        ];
    }

    /**
     * @param int $id
     * @param string $label
     * @return CustomerUserRole
     */
    protected function getRole($id, $label)
    {
        $role = new CustomerUserRole($label);

        $reflection = new \ReflectionProperty(get_class($role), 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($role, $id);

        $role->setLabel($label);

        return $role;
    }
}
