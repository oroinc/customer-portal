<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRoleSelectType;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendCustomerUserRoleSelectTypeTest extends FormIntegrationTestCase
{
    /** @var FrontendCustomerUserRoleSelectType */
    private $formType;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    protected function setUp(): void
    {
        $customer = $this->getCustomer(1, 'customer');
        $organization = $this->getOrganization(1);
        $user = new CustomerUser();

        $user->setCustomer($customer);
        $user->setOrganization($organization);
        $tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $tokenAccessor->expects($this->any())
            ->method('getUser')
            ->willReturn($user);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $repo = $this->createMock(CustomerUserRoleRepository::class);
        $repo->expects($this->any())
            ->method('createQueryBuilder')
            ->with('customer')
            ->willReturn($this->createMock(QueryBuilder::class));
        $em = $this->createMock(ObjectManager::class);
        $em->expects($this->any())
            ->method('getRepository')
            ->with(CustomerUserRole::class)
            ->willReturn($repo);
        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($em);
        $this->formType = new FrontendCustomerUserRoleSelectType(
            $tokenAccessor,
            $this->registry
        );
        $this->formType->setRoleClass(CustomerUserRole::class);

        parent::setUp();
    }

    private function getCustomer(int $id, string $name): Customer
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, $id);
        $customer->setName($name);

        return $customer;
    }

    private function getOrganization(int $id): Organization
    {
        $organization = new Organization();
        $organization->setId($id);

        return $organization;
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
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'))
            ->willReturnCallback(
                function (array $options) {
                    $this->assertArrayHasKey('query_builder', $options);
                    $this->assertInstanceOf(\Closure::class, $options['query_builder']);
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
        $tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn(null);
        $resolver = $this->createMock(OptionsResolver::class);
        $roleFormType = new FrontendCustomerUserRoleSelectType($tokenAccessor, $this->registry);
        $roleFormType->configureOptions($resolver);
    }
}
