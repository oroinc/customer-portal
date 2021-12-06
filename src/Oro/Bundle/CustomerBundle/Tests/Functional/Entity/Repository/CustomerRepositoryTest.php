<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\CatalogBundle\Tests\Functional\DataFixtures\LoadCategoryData;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadDuplicatedCustomer;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\VisibilityInterface;

class CustomerRepositoryTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadDuplicatedCustomer::class]);
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
}
