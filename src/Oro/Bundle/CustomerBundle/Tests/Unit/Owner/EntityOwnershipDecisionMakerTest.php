<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Owner;

use Doctrine\Inflector\Rules\English\InflectorFactory;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\CustomerBundle\Owner\EntityOwnershipDecisionMaker;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\CustomerBundle\Tests\Unit\Owner\Fixtures\Entity\TestEntity;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Owner\EntityOwnerAccessor;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProvider;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\Organization;
use Oro\Bundle\SecurityBundle\Tests\Unit\Stub\OwnershipMetadataProviderStub;
use Oro\Component\Testing\ReflectionUtil;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class EntityOwnershipDecisionMakerTest extends \PHPUnit\Framework\TestCase
{
    private const ORG_ID = 10;
    private const CUSTOMER_ID = 100;
    private const CUSTOMER_USER_ID = 10000;

    /** @var \PHPUnit\Framework\MockObject\MockObject|EntityOwnershipDecisionMaker */
    private $decisionMaker;

    /** @var \PHPUnit\Framework\MockObject\MockObject|TokenAccessorInterface */
    private $tokenAccessor;

    private OwnerTree $tree;

    private OwnershipMetadataProviderStub $metadataProvider;

    private Organization $org1;
    private Organization $org2;
    private Organization $org3;
    private Organization $org4;

    private Customer $cust1;
    private Customer $cust2;
    private Customer $cust3;
    private Customer $cust31;
    private Customer $cust4;
    private Customer $cust41;
    private Customer $cust411;

    private CustomerUser $custUsr1;
    private CustomerUser $custUsr2;
    private CustomerUser $custUsr3;
    private CustomerUser $custUsr31;
    private CustomerUser $custUsr4;
    private CustomerUser $custUsr411;

    protected function setUp(): void
    {
        $this->tree = new OwnerTree();

        $this->metadataProvider = new OwnershipMetadataProviderStub($this, ['user' => CustomerUser::class]);
        $this->metadataProvider->setMetadata(
            $this->metadataProvider->getOrganizationClass(),
            new FrontendOwnershipMetadata()
        );
        $this->metadataProvider->setMetadata(
            $this->metadataProvider->getBusinessUnitClass(),
            new FrontendOwnershipMetadata('FRONTEND_CUSTOMER', 'customer', 'customer_id', 'organization')
        );
        $this->metadataProvider->setMetadata(
            $this->metadataProvider->getUserClass(),
            new FrontendOwnershipMetadata(
                'FRONTEND_USER',
                'customerUser',
                'customer_user_id',
                'organization',
                'organization_id',
                'customer',
                'customer_id'
            )
        );

        $treeProvider = $this->createMock(OwnerTreeProvider::class);
        $treeProvider->expects($this->any())
            ->method('getTree')
            ->willReturn($this->tree);

        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $doctrineHelper = $this->createMock(DoctrineHelper::class);

        $repository = $this->createMock(CustomerRepository::class);
        $repository->expects($this->any())
            ->method('getChildrenIds')
            ->willReturnMap(
                [
                    [self::CUSTOMER_ID + 1, null, []],
                    [self::CUSTOMER_ID + 2, null, []],
                    [self::CUSTOMER_ID + 3, null, [self::CUSTOMER_ID + 31]],
                    [self::CUSTOMER_ID + 31, null, []],
                    [self::CUSTOMER_ID + 32, null, [self::CUSTOMER_ID + 321]],
                    [self::CUSTOMER_ID + 321, null, []],
                    [self::CUSTOMER_ID + 4, null, [self::CUSTOMER_ID + 41, self::CUSTOMER_ID + 411]],
                    [self::CUSTOMER_ID + 41, null, [self::CUSTOMER_ID + 411]],
                    [self::CUSTOMER_ID + 411, null, []],
                ]
            );

        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->any())
            ->method('getRepository')
            ->with(Customer::class)
            ->willReturn($repository);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->with(Customer::class)
            ->willReturn($manager);

        $this->decisionMaker = new EntityOwnershipDecisionMaker(
            $treeProvider,
            new ObjectIdAccessor($doctrineHelper),
            new EntityOwnerAccessor($this->metadataProvider, (new InflectorFactory())->build()),
            $this->metadataProvider,
            $this->tokenAccessor,
            $doctrine,
            PropertyAccess::createPropertyAccessor()
        );
    }

    private function getCustomer(int $id, Customer $parent = null): Customer
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, $id);
        if (null !== $parent) {
            $customer->setParent($parent);
        }

        return $customer;
    }

    private function getCustomerUser(int $id, Organization $organization, Customer $customer = null): CustomerUser
    {
        $customerUser = new CustomerUser();
        ReflectionUtil::setId($customerUser, $id);
        $customerUser->setOrganization($organization);
        if (null !== $customer) {
            $customerUser->setCustomer($customer);
        }

        return $customerUser;
    }

    private function buildTestTree(): void
    {
        $this->org1 = new Organization(self::ORG_ID + 1);
        $this->org2 = new Organization(self::ORG_ID + 2);
        $this->org3 = new Organization(self::ORG_ID + 3);
        $this->org4 = new Organization(self::ORG_ID + 4);

        $this->cust1 = $this->getCustomer(self::CUSTOMER_ID + 1);
        $this->cust2 = $this->getCustomer(self::CUSTOMER_ID + 2);
        $this->cust3 = $this->getCustomer(self::CUSTOMER_ID + 3);
        $this->cust31 = $this->getCustomer(self::CUSTOMER_ID + 31);
        $this->cust4 = $this->getCustomer(self::CUSTOMER_ID + 4);
        $this->cust41 = $this->getCustomer(self::CUSTOMER_ID + 41, $this->cust4);
        $this->cust411 = $this->getCustomer(self::CUSTOMER_ID + 411, $this->cust41);

        $this->custUsr1 = $this->getCustomerUser(self::CUSTOMER_USER_ID + 1, $this->org1);
        $this->custUsr2 = $this->getCustomerUser(self::CUSTOMER_USER_ID + 2, $this->org2, $this->cust2);
        $this->custUsr3 = $this->getCustomerUser(self::CUSTOMER_USER_ID + 3, $this->org3, $this->cust3);
        $this->custUsr31 = $this->getCustomerUser(self::CUSTOMER_USER_ID + 31, $this->org3, $this->cust31);
        $this->custUsr4 = $this->getCustomerUser(self::CUSTOMER_USER_ID + 4, $this->org4, $this->cust4);
        $this->custUsr411 = $this->getCustomerUser(self::CUSTOMER_USER_ID + 411, $this->org4, $this->cust411);

        $this->tree->addBusinessUnit(self::CUSTOMER_ID + 1, null);
        $this->tree->addBusinessUnit(self::CUSTOMER_ID + 2, null);
        $this->tree->addBusinessUnit(self::CUSTOMER_ID + 3, self::ORG_ID + 3);
        $this->tree->addBusinessUnit(self::CUSTOMER_ID + 31, self::ORG_ID + 3);
        $this->tree->addBusinessUnit(self::CUSTOMER_ID + 32, self::ORG_ID + 3);
        $this->tree->addBusinessUnit(self::CUSTOMER_ID + 321, self::ORG_ID + 3);
        $this->tree->addBusinessUnit(self::CUSTOMER_ID + 4, self::ORG_ID + 4);
        $this->tree->addBusinessUnit(self::CUSTOMER_ID + 41, self::ORG_ID + 4);
        $this->tree->addBusinessUnit(self::CUSTOMER_ID + 411, self::ORG_ID + 4);

        $subordinateBusinessUnits  = [
            self::CUSTOMER_ID + 3  => [self::CUSTOMER_ID + 31],
            self::CUSTOMER_ID + 32 => [self::CUSTOMER_ID + 321],
            self::CUSTOMER_ID + 41 => [self::CUSTOMER_ID + 411],
            self::CUSTOMER_ID + 4  => [self::CUSTOMER_ID + 41, self::CUSTOMER_ID + 411],
        ];

        foreach ($subordinateBusinessUnits as $parentBuId => $buIds) {
            $this->tree->setSubordinateBusinessUnitIds($parentBuId, $buIds);
        }

        $this->tree->addUser(self::CUSTOMER_USER_ID + 1, null);
        $this->tree->addUser(self::CUSTOMER_USER_ID + 2, self::CUSTOMER_ID + 2);
        $this->tree->addUser(self::CUSTOMER_USER_ID + 3, self::CUSTOMER_ID + 3);
        $this->tree->addUser(self::CUSTOMER_USER_ID + 31, self::CUSTOMER_ID + 31);
        $this->tree->addUser(self::CUSTOMER_USER_ID + 4, self::CUSTOMER_ID + 4);
        $this->tree->addUser(self::CUSTOMER_USER_ID + 41, self::CUSTOMER_ID + 41);
        $this->tree->addUser(self::CUSTOMER_USER_ID + 411, self::CUSTOMER_ID + 411);

        $this->tree->addUserOrganization(self::CUSTOMER_USER_ID + 1, self::ORG_ID + 1);
        $this->tree->addUserOrganization(self::CUSTOMER_USER_ID + 1, self::ORG_ID + 2);
        $this->tree->addUserOrganization(self::CUSTOMER_USER_ID + 2, self::ORG_ID + 2);
        $this->tree->addUserOrganization(self::CUSTOMER_USER_ID + 3, self::ORG_ID + 2);
        $this->tree->addUserOrganization(self::CUSTOMER_USER_ID + 3, self::ORG_ID + 3);
        $this->tree->addUserOrganization(self::CUSTOMER_USER_ID + 31, self::ORG_ID + 3);
        $this->tree->addUserOrganization(self::CUSTOMER_USER_ID + 4, self::ORG_ID + 4);
        $this->tree->addUserOrganization(self::CUSTOMER_USER_ID + 411, self::ORG_ID + 4);

        $this->tree->addUserBusinessUnit(self::CUSTOMER_USER_ID + 1, self::ORG_ID + 1, self::CUSTOMER_ID + 1);
        $this->tree->addUserBusinessUnit(self::CUSTOMER_USER_ID + 1, self::ORG_ID + 2, self::CUSTOMER_ID + 2);
        $this->tree->addUserBusinessUnit(self::CUSTOMER_USER_ID + 2, self::ORG_ID + 2, self::CUSTOMER_ID + 2);
        $this->tree->addUserBusinessUnit(self::CUSTOMER_USER_ID + 3, self::ORG_ID + 3, self::CUSTOMER_ID + 3);
        $this->tree->addUserBusinessUnit(self::CUSTOMER_USER_ID + 3, self::ORG_ID + 2, self::CUSTOMER_ID + 2);
        $this->tree->addUserBusinessUnit(self::CUSTOMER_USER_ID + 31, self::ORG_ID + 3, self::CUSTOMER_ID + 31);
        $this->tree->addUserBusinessUnit(self::CUSTOMER_USER_ID + 4, self::ORG_ID + 4, self::CUSTOMER_ID + 4);
        $this->tree->addUserBusinessUnit(self::CUSTOMER_USER_ID + 411, self::ORG_ID + 4, self::CUSTOMER_ID + 411);
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(?object $user, bool $expectedResult)
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals($expectedResult, $this->decisionMaker->supports());
    }

    public function supportsDataProvider(): array
    {
        return [
            'without user' => [
                'user' => null,
                'expectedResult' => false,
            ],
            'unsupported user' => [
                'user' => new \stdClass(),
                'expectedResult' => false,
            ],
            'supported user' => [
                'user' => new CustomerUser(),
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @dataProvider isAssociatedWithBusinessUnitDataProvider
     */
    public function testIsAssociatedWithBusinessUnitWhenNoCustomerUserForDomainObject(
        string $organization,
        string $customer,
        string $customerUser,
        bool $deep,
        bool $isAssociated
    ) {
        $this->buildTestTree();
        $this->configureMetadataProvider();

        $domainObject = new TestEntity(1, null, $this->{$organization}, null, $this->{$customer});

        $this->assertEquals(
            $isAssociated,
            $this->decisionMaker->isAssociatedWithBusinessUnit($this->{$customerUser}, $domainObject, $deep)
        );
    }

    public function isAssociatedWithBusinessUnitDataProvider(): array
    {
        return [
            'with local access and same customer' => [
                'organization' => 'org2',
                'customer' => 'cust2',
                'customerUser' => 'custUsr2',
                'deep' => false,
                'isAssociated' => true
            ],
            'with deep access and same customer' => [
                'organization' => 'org2',
                'customer' => 'cust2',
                'customerUser' => 'custUsr2',
                'deep' => true,
                'isAssociated' => true
            ],
            'with local access and subordinate customer' => [
                'organization' => 'org4',
                'customer' => 'cust411',
                'customerUser' => 'custUsr4',
                'deep' => false,
                'isAssociated' => false
            ],
            'with deep access and subordinate customer' => [
                'organization' => 'org4',
                'customer' => 'cust411',
                'customerUser' => 'custUsr4',
                'deep' => true,
                'isAssociated' => true
            ],
            'with local access and not subordinate customer' => [
                'organization' => 'org4',
                'customer' => 'cust4',
                'customerUser' => 'custUsr411',
                'deep' => false,
                'isAssociated' => false
            ],
            'with deep access and not subordinate customer' => [
                'organization' => 'org4',
                'customer' => 'cust4',
                'customerUser' => 'custUsr411',
                'deep' => true,
                'isAssociated' => false
            ],
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testIsAssociatedWithBusinessUnit()
    {
        $this->buildTestTree();
        $this->configureMetadataProvider();

        $obj = new TestEntity(1, null, $this->org1);
        $obj1 = new TestEntity(1, null, $this->org1, $this->custUsr1, $this->cust1);
        $obj2 = new TestEntity(1, null, $this->org2, $this->custUsr2, $this->cust2);
        $obj3 = new TestEntity(1, null, $this->org3, $this->custUsr3, $this->cust3);
        $obj31 = new TestEntity(1, null, $this->org3, $this->custUsr31, $this->cust31);
        $obj4 = new TestEntity(1, null, $this->org4, $this->custUsr4, $this->cust41);
        $obj411 = new TestEntity(1, null, $this->org4, $this->custUsr411, $this->cust411);

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj1));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj1, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj2));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj2, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj3, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj31));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj31, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj4));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj4, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj411));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj411, true));

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj1));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj1, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj2));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj2, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj3, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj31));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj31, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj4));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj4, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj411));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj411, true));

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj1));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj1, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj2));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj2, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj3, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj31, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj4));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj4, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj411));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj411, true));

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj1, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj2, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj3, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj31, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj4));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj4, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj411));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj411, true));

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj1, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj2, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj3, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj31));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj31, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj4));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj4, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj411));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj411, true));

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj1, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj2, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj3, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj31));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj31, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj4));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj4, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj411));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj411, true));
    }

    private function configureMetadataProvider(): void
    {
        $this->metadataProvider->setMetadata(
            TestEntity::class,
            new FrontendOwnershipMetadata(
                'FRONTEND_USER',
                'customerUser',
                'customer_user_id',
                'organization',
                'organization_id',
                'customer',
                'customer_id'
            )
        );
    }
}
