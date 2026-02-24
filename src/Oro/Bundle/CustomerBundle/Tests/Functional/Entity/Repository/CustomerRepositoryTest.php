<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\CatalogBundle\Tests\Functional\DataFixtures\LoadCategoryData;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomer;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerWithCycleRelation;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadDuplicatedCustomer;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\VisibilityInterface;

/**
 * @dbIsolationPerTest
 */
class CustomerRepositoryTest extends WebTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
    }

    private function getRepository(): CustomerRepository
    {
        return self::getContainer()->get('doctrine')->getRepository(Customer::class);
    }

    private function getAclHelper(): AclHelper
    {
        return self::getContainer()->get('oro_security.acl_helper');
    }

    /**
     * @dataProvider customerReferencesDataProvider
     */
    public function testGetChildrenIds(string $referenceName, array $expectedReferences, bool $withAclCheck = true)
    {
        $this->loadFixtures([LoadDuplicatedCustomer::class]);

        /** @var Customer $customer */
        $customer = $this->getReference($referenceName);

        $expected = [];
        foreach ($expectedReferences as $reference) {
            $expected[] = $this->getReference($reference)->getId();
        }
        $childrenIds = $this->getRepository()->getChildrenIds(
            $customer->getId(),
            $withAclCheck ? $this->getAclHelper() : null
        );
        sort($expected);
        sort($childrenIds);

        $this->assertEquals($expected, $childrenIds);
    }

    public function customerReferencesDataProvider(): array
    {
        return [
            'orphan' => [
                'customer.orphan',
                []
            ],
            'level_1' => [
                'customer.level_1',
                [
                    'customer.level_1.1',
                    'customer.level_1.1.1',
                    'customer.level_1.1.2',
                    'customer.level_1.2',
                    'customer.level_1.2.1',
                    'customer.level_1.2.1.1',
                    'customer.level_1.3',
                    'customer.level_1.3.1',
                    'customer.level_1.3.1.1',
                    'customer.level_1.4',
                    'customer.level_1.4.1',
                    'customer.level_1.4.1.1',
                ]
            ],
            'level_1.1' => [
                'customer.level_1.1',
                [
                    'customer.level_1.1.1',
                    'customer.level_1.1.2',
                ]
            ],
            'level_1.2' => [
                'customer.level_1.2',
                [
                    'customer.level_1.2.1',
                    'customer.level_1.2.1.1',
                ]
            ],
            'level_1.3' => [
                'customer.level_1.3',
                [
                    'customer.level_1.3.1',
                    'customer.level_1.3.1.1',
                ]
            ],
            'level_1.4' => [
                'customer.level_1.4',
                [
                    'customer.level_1.4.1',
                    'customer.level_1.4.1.1',
                ]
            ],
            'without acl' => [
                'customer.level_1',
                [
                    'customer.level_1.1',
                    'customer.level_1.1.1',
                    'customer.level_1.1.2',
                    'customer.level_1.2',
                    'customer.level_1.2.1',
                    'customer.level_1.2.1.1',
                    'customer.level_1.3',
                    'customer.level_1.3.1',
                    'customer.level_1.3.1.1',
                    'customer.level_1.4',
                    'customer.level_1.4.1',
                    'customer.level_1.4.1.1'
                ],
                false
            ],
        ];
    }

    public function getCategoryCustomerIdsByVisibilityDataProvider(): array
    {
        return [
            'FIRST_LEVEL with VISIBLE' => [
                'categoryName' => LoadCategoryData::FIRST_LEVEL,
                'visibility' => VisibilityInterface::VISIBLE,
                'expectedCustomers' => [
                    'customer.level_1.4',
                ]
            ],
            'FIRST_LEVEL with VISIBLE restricted' => [
                'categoryName' => LoadCategoryData::FIRST_LEVEL,
                'visibility' => VisibilityInterface::VISIBLE,
                'expectedCustomers' => [],
                'restricted' => []
            ],
            'FIRST_LEVEL with HIDDEN' => [
                'categoryName' => LoadCategoryData::FIRST_LEVEL,
                'visibility' => VisibilityInterface::HIDDEN,
                'expectedCustomers' => [
                    'customer.level_1.1',
                ]
            ],
        ];
    }

    public function testGetBatchIterator()
    {
        $this->loadFixtures([LoadDuplicatedCustomer::class]);

        /** @var Customer[] $results */
        $results = $this->getRepository()->findAll();
        $customers = [];

        foreach ($results as $customer) {
            $customers[$customer->getId()] = $customer;
        }

        $customersQuantity = count($customers);
        $customersIterator = $this->getRepository()->getBatchIterator();
        $iteratorQuantity = 0;
        foreach ($customersIterator as $customer) {
            ++$iteratorQuantity;
            unset($customers[$customer->getId()]);
        }

        $this->assertEquals($customersQuantity, $iteratorQuantity);
        $this->assertEmpty($customers);
    }

    public function testGetIdsByCustomerGroup()
    {
        $this->loadFixtures([LoadDuplicatedCustomer::class]);

        /** @var CustomerGroup $customerGroup */
        $customerGroup = $this->getReference('customer_group.group3');

        /** @var Customer $customer131 */
        $customer131 = $this->getReference('customer.level_1.3.1');
        /** @var Customer $customer1311 */
        $customer1311 = $this->getReference('customer.level_1.3.1.1');
        /** @var Customer $customer14 */
        $customer14 = $this->getReference('customer.level_1.4');

        $actual = $this->getRepository()->getIdsByCustomerGroup($customerGroup);

        $this->assertCount(3, $actual);
        self::assertContainsEquals($customer131->getId(), $actual, var_export($actual, true));
        self::assertContainsEquals($customer1311->getId(), $actual, var_export($actual, true));
        self::assertContainsEquals($customer14->getId(), $actual, var_export($actual, true));
    }

    public function testGetCustomerGroupFirstCustomer(): void
    {
        $this->loadFixtures([LoadDuplicatedCustomer::class]);

        $customerGroup = $this->getReference('customer_group.group1');
        $customer = $this->getReference('customer.level_1');

        self::assertEquals($customer, $this->getRepository()->getCustomerGroupFirstCustomer($customerGroup));
    }

    public function testGetAssignableCustomerIds()
    {
        $this->loadFixtures([LoadCustomer::class]);

        $this->assertEqualsCanonicalizing(
            [
                $this->getReference('customer')->getId()
            ],
            $this->getRepository()->getAssignableCustomerIds(
                self::getContainer()->get('oro_security.acl_helper'),
                Customer::class
            )
        );
    }

    /**
     * @dataProvider getRootCustomerIdDataProvider
     */
    public function testGetRootCustomerId(string $customerReference, string $expectedRootReference): void
    {
        $this->loadFixtures([LoadCustomerWithCycleRelation::class]);

        /** @var Customer $customer */
        $customer = $this->getReference($customerReference);
        /** @var Customer $expectedRoot */
        $expectedRoot = $this->getReference($expectedRootReference);

        $this->assertEquals(
            $expectedRoot->getId(),
            $this->getRepository()->getRootCustomerId($customer->getId())
        );
    }

    public function getRootCustomerIdDataProvider(): array
    {
        return [
            'root customer returns itself' => [
                'customerReference' => LoadCustomerWithCycleRelation::CUSTOMER_LEVEL_1,
                'expectedRootReference' => LoadCustomerWithCycleRelation::CUSTOMER_LEVEL_1,
            ],
            'orphan customer returns itself' => [
                'customerReference' => LoadCustomerWithCycleRelation::DEFAULT_ACCOUNT_NAME,
                'expectedRootReference' => LoadCustomerWithCycleRelation::DEFAULT_ACCOUNT_NAME,
            ],
            'level 2 customer returns root' => [
                'customerReference' => LoadCustomerWithCycleRelation::CUSTOMER_LEVEL_1_DOT_1,
                'expectedRootReference' => LoadCustomerWithCycleRelation::CUSTOMER_LEVEL_1,
            ],
            'level 3 customer returns root' => [
                'customerReference' => LoadCustomerWithCycleRelation::CUSTOMER_LEVEL_1_DOT_1_DOT_2,
                'expectedRootReference' => LoadCustomerWithCycleRelation::CUSTOMER_LEVEL_1,
            ],
            'level 4 customer returns root' => [
                'customerReference' => LoadCustomerWithCycleRelation::CUSTOMER_LEVEL_1_DOT_2_DOT_1_DOT_1,
                'expectedRootReference' => LoadCustomerWithCycleRelation::CUSTOMER_LEVEL_1,
            ],
            'level 4.1 with cycle related parents' => [
                'customerReference' => LoadCustomerWithCycleRelation::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1_DOT_2,
                'expectedRootReference' => LoadCustomerWithCycleRelation::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1_DOT_2,
            ],
            'level 4.1 with cycle relation parent to self' => [
                'customerReference' => LoadCustomerWithCycleRelation::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1_DOT_3,
                'expectedRootReference' => LoadCustomerWithCycleRelation::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1_DOT_3,
            ],
        ];
    }
}
