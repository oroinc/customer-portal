<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Owner;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Owner\FrontendOwnerTreeProvider;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadTreeProviderCustomers;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadTreeProviderCustomerUserData;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData as MainLoadCustomerUserData;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\SecurityBundle\Test\OwnerTreeWrappingPropertiesAccessor;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Component\Testing\QueryTracker;

/**
 * @dbIsolationPerTest
 */
class FrontendOwnerTreeProviderTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadTreeProviderCustomerUserData::class, LoadOrganization::class]);
    }

    /**
     * @dataProvider getTreeDataProvider
     */
    public function testGetTree(array $token, array $expectedTreeData): void
    {
        $this->createToken(
            $token['customerUserReference'],
            $token['customerUserPassword'],
            $token['organizationReference']
        );

        $ownerTree = $this->getFrontendOwnerTreeProvider()->getTree();

        $expectedData = [];
        foreach ($expectedTreeData as $property => $data) {
            $expectedData[$property] = $this->prepareTreeData($data);
        }

        $this->assertOwnerTreeEquals($expectedData, $ownerTree);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getTreeDataProvider(): array
    {
        return [
            'level 1 customer user' => [
                'token' => [
                    'customerUserReference' => LoadCustomerUserData::LEVEL_1_EMAIL,
                    'customerUserPassword' => LoadCustomerUserData::LEVEL_1_PASSWORD,
                    'organizationReference' => LoadOrganization::ORGANIZATION
                ],
                'treeData' => [
                    'userOwningOrganizationId' => [
                        LoadCustomerUserData::LEVEL_1_EMAIL => LoadOrganization::ORGANIZATION,
                        LoadCustomerUserData::LEVEL_1_1_EMAIL => LoadOrganization::ORGANIZATION,
                        LoadCustomerUserData::GROUP2_EMAIL => LoadOrganization::ORGANIZATION,
                        LoadCustomerUserData::EMAIL => LoadOrganization::ORGANIZATION
                    ],
                    'userOrganizationIds' => [
                        LoadCustomerUserData::LEVEL_1_EMAIL => [LoadOrganization::ORGANIZATION],
                        LoadCustomerUserData::LEVEL_1_1_EMAIL => [LoadOrganization::ORGANIZATION],
                        LoadCustomerUserData::GROUP2_EMAIL => [LoadOrganization::ORGANIZATION],
                        LoadCustomerUserData::EMAIL => [LoadOrganization::ORGANIZATION]
                    ],
                    'userOwningBusinessUnitId' => [
                        LoadCustomerUserData::LEVEL_1_EMAIL => LoadCustomers::CUSTOMER_LEVEL_1,
                        LoadCustomerUserData::LEVEL_1_1_EMAIL => LoadCustomers::CUSTOMER_LEVEL_1_DOT_1,
                        LoadCustomerUserData::GROUP2_EMAIL => LoadCustomers::CUSTOMER_LEVEL_1_DOT_2,
                        LoadCustomerUserData::EMAIL => LoadCustomers::CUSTOMER_LEVEL_1
                    ],
                    'userBusinessUnitIds' => [
                        LoadCustomerUserData::LEVEL_1_EMAIL => [LoadCustomers::CUSTOMER_LEVEL_1],
                        LoadCustomerUserData::LEVEL_1_1_EMAIL => [LoadCustomers::CUSTOMER_LEVEL_1_DOT_1],
                        LoadCustomerUserData::GROUP2_EMAIL => [LoadCustomers::CUSTOMER_LEVEL_1_DOT_2],
                        LoadCustomerUserData::EMAIL => [LoadCustomers::CUSTOMER_LEVEL_1]
                    ],
                    'userOrganizationBusinessUnitIds' => [
                        LoadCustomerUserData::LEVEL_1_EMAIL => [
                            LoadOrganization::ORGANIZATION => [LoadCustomers::CUSTOMER_LEVEL_1]
                        ],
                        LoadCustomerUserData::LEVEL_1_1_EMAIL => [
                            LoadOrganization::ORGANIZATION => [LoadCustomers::CUSTOMER_LEVEL_1_DOT_1],
                        ],
                        LoadCustomerUserData::GROUP2_EMAIL => [
                            LoadOrganization::ORGANIZATION => [LoadCustomers::CUSTOMER_LEVEL_1_DOT_2]
                        ],
                        LoadCustomerUserData::EMAIL => [
                            LoadOrganization::ORGANIZATION => [LoadCustomers::CUSTOMER_LEVEL_1]
                        ]
                    ],
                    'businessUnitOwningOrganizationId' => [
                        LoadCustomers::CUSTOMER_LEVEL_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_2 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_3 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_4 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1 => LoadOrganization::ORGANIZATION
                    ],
                    'assignedBusinessUnitUserIds' => [
                        LoadCustomers::CUSTOMER_LEVEL_1 => [
                            LoadCustomerUserData::EMAIL,
                            LoadCustomerUserData::LEVEL_1_EMAIL
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_1 => [
                            LoadCustomerUserData::LEVEL_1_1_EMAIL
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2 => [
                            LoadCustomerUserData::GROUP2_EMAIL
                        ]
                    ],
                    'subordinateBusinessUnitIds' => [
                        LoadCustomers::CUSTOMER_LEVEL_1 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_2,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_1 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_2
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_3 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_4 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1
                        ]
                    ],
                    'organizationBusinessUnitIds' => [
                        LoadOrganization::ORGANIZATION => [
                            LoadCustomers::CUSTOMER_LEVEL_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_2,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1
                        ]
                    ],
                ]
            ],
            'the same tree data as for level 1 is loaded for group 2 email customer user as they share same tree' => [
                'token' => [
                    'customerUserReference' => LoadCustomerUserData::GROUP2_EMAIL,
                    'customerUserPassword' => LoadCustomerUserData::GROUP2_PASSWORD,
                    'organizationReference' => LoadOrganization::ORGANIZATION
                ],
                'treeData' => [
                    'userOwningOrganizationId' => [
                        LoadCustomerUserData::LEVEL_1_EMAIL => LoadOrganization::ORGANIZATION,
                        LoadCustomerUserData::LEVEL_1_1_EMAIL => LoadOrganization::ORGANIZATION,
                        LoadCustomerUserData::GROUP2_EMAIL => LoadOrganization::ORGANIZATION,
                        LoadCustomerUserData::EMAIL => LoadOrganization::ORGANIZATION
                    ],
                    'userOrganizationIds' => [
                        LoadCustomerUserData::LEVEL_1_EMAIL => [LoadOrganization::ORGANIZATION],
                        LoadCustomerUserData::LEVEL_1_1_EMAIL => [LoadOrganization::ORGANIZATION],
                        LoadCustomerUserData::GROUP2_EMAIL => [LoadOrganization::ORGANIZATION],
                        LoadCustomerUserData::EMAIL => [LoadOrganization::ORGANIZATION]
                    ],
                    'userOwningBusinessUnitId' => [
                        LoadCustomerUserData::LEVEL_1_EMAIL => LoadCustomers::CUSTOMER_LEVEL_1,
                        LoadCustomerUserData::LEVEL_1_1_EMAIL => LoadCustomers::CUSTOMER_LEVEL_1_DOT_1,
                        LoadCustomerUserData::GROUP2_EMAIL => LoadCustomers::CUSTOMER_LEVEL_1_DOT_2,
                        LoadCustomerUserData::EMAIL => LoadCustomers::CUSTOMER_LEVEL_1
                    ],
                    'userBusinessUnitIds' => [
                        LoadCustomerUserData::LEVEL_1_EMAIL => [LoadCustomers::CUSTOMER_LEVEL_1],
                        LoadCustomerUserData::LEVEL_1_1_EMAIL => [LoadCustomers::CUSTOMER_LEVEL_1_DOT_1],
                        LoadCustomerUserData::GROUP2_EMAIL => [LoadCustomers::CUSTOMER_LEVEL_1_DOT_2],
                        LoadCustomerUserData::EMAIL => [LoadCustomers::CUSTOMER_LEVEL_1]
                    ],
                    'userOrganizationBusinessUnitIds' => [
                        LoadCustomerUserData::LEVEL_1_EMAIL => [
                            LoadOrganization::ORGANIZATION => [LoadCustomers::CUSTOMER_LEVEL_1]
                        ],
                        LoadCustomerUserData::LEVEL_1_1_EMAIL => [
                            LoadOrganization::ORGANIZATION => [LoadCustomers::CUSTOMER_LEVEL_1_DOT_1],
                        ],
                        LoadCustomerUserData::GROUP2_EMAIL => [
                            LoadOrganization::ORGANIZATION => [LoadCustomers::CUSTOMER_LEVEL_1_DOT_2]
                        ],
                        LoadCustomerUserData::EMAIL => [
                            LoadOrganization::ORGANIZATION => [LoadCustomers::CUSTOMER_LEVEL_1]
                        ]
                    ],
                    'businessUnitOwningOrganizationId' => [
                        LoadCustomers::CUSTOMER_LEVEL_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_2 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_3 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_4 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1 => LoadOrganization::ORGANIZATION
                    ],
                    'assignedBusinessUnitUserIds' => [
                        LoadCustomers::CUSTOMER_LEVEL_1 => [
                            LoadCustomerUserData::EMAIL,
                            LoadCustomerUserData::LEVEL_1_EMAIL
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_1 => [
                            LoadCustomerUserData::LEVEL_1_1_EMAIL
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2 => [
                            LoadCustomerUserData::GROUP2_EMAIL
                        ]
                    ],
                    'subordinateBusinessUnitIds' => [
                        LoadCustomers::CUSTOMER_LEVEL_1 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_2,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_1 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_2
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_3 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_4 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1
                        ]
                    ],
                    'organizationBusinessUnitIds' => [
                        LoadOrganization::ORGANIZATION => [
                            LoadCustomers::CUSTOMER_LEVEL_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_2,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1
                        ]
                    ],
                ]
            ],
            'orphan customer user has its own tree' => [
                'token' => [
                    'customerUserReference' => LoadCustomerUserData::ORPHAN_EMAIL,
                    'customerUserPassword' => LoadCustomerUserData::ORPHAN_PASSWORD,
                    'organizationReference' => LoadOrganization::ORGANIZATION
                ],
                'treeData' => [
                    'userOwningOrganizationId' => [
                        LoadCustomerUserData::ORPHAN_EMAIL => LoadOrganization::ORGANIZATION
                    ],
                    'userOrganizationIds' => [
                        LoadCustomerUserData::ORPHAN_EMAIL => [LoadOrganization::ORGANIZATION]
                    ],
                    'userOwningBusinessUnitId' => [
                        LoadCustomerUserData::ORPHAN_EMAIL => LoadCustomers::DEFAULT_ACCOUNT_NAME
                    ],
                    'userBusinessUnitIds' => [
                        LoadCustomerUserData::ORPHAN_EMAIL => [LoadCustomers::DEFAULT_ACCOUNT_NAME]
                    ],
                    'userOrganizationBusinessUnitIds' => [
                        LoadCustomerUserData::ORPHAN_EMAIL => [
                            LoadOrganization::ORGANIZATION => [LoadCustomers::DEFAULT_ACCOUNT_NAME]
                        ],
                    ],
                    'businessUnitOwningOrganizationId' => [
                        LoadCustomers::DEFAULT_ACCOUNT_NAME => LoadOrganization::ORGANIZATION
                    ],
                    'assignedBusinessUnitUserIds' => [
                        LoadCustomers::DEFAULT_ACCOUNT_NAME => [
                            LoadCustomerUserData::ORPHAN_EMAIL,
                        ]
                    ],
                    'subordinateBusinessUnitIds' => [],
                    'organizationBusinessUnitIds' => [
                        LoadOrganization::ORGANIZATION => [LoadCustomers::DEFAULT_ACCOUNT_NAME]
                    ],
                ]
            ],
            'сustomers have an inconsistent hierarchy in tree' => [
                'token' => [
                    'customerUserReference' => LoadTreeProviderCustomerUserData::LEVEL_1_2_EMAIL,
                    'customerUserPassword' => LoadTreeProviderCustomerUserData::LEVEL_1_2_PASSWORD,
                    'organizationReference' => LoadOrganization::ORGANIZATION
                ],
                'treeData' => [
                    'userOwningOrganizationId' => [LoadOrganization::ORGANIZATION],
                    'userOrganizationIds' => [
                        LoadTreeProviderCustomerUserData::LEVEL_1_2_EMAIL => [LoadOrganization::ORGANIZATION]
                    ],
                    'userOwningBusinessUnitId' => [
                        LoadTreeProviderCustomerUserData::LEVEL_1_2_EMAIL =>
                            LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2
                    ],
                    'userBusinessUnitIds' => [
                        LoadTreeProviderCustomerUserData::LEVEL_1_2_EMAIL =>
                            [LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2]
                    ],
                    'userOrganizationBusinessUnitIds' => [
                        LoadTreeProviderCustomerUserData::LEVEL_1_2_EMAIL => [
                            LoadOrganization::ORGANIZATION => [LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2]
                        ],
                    ],
                    'businessUnitOwningOrganizationId' => [
                        LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2 => LoadOrganization::ORGANIZATION,
                        LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2_DOT_1_DOT_1 => LoadOrganization::ORGANIZATION,
                    ],
                    'assignedBusinessUnitUserIds' => [
                        LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2 => [
                            LoadTreeProviderCustomerUserData::LEVEL_1_2_EMAIL,
                        ]
                    ],
                    'subordinateBusinessUnitIds' => [
                        [
                            LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2_DOT_1_DOT_1,
                        ],
                        [
                            LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2_DOT_1,
                            LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2_DOT_1_DOT_1,
                        ]
                    ],
                    'organizationBusinessUnitIds' => [
                        LoadOrganization::ORGANIZATION => [
                            LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2,
                            LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2_DOT_1,
                            LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2_DOT_1_DOT_1
                        ]
                    ],
                ]
            ]
        ];
    }

    /**
     * @dataProvider getTreeNoLoggedInCustomerUserDataProvider
     */
    public function testGetTreeWhenNoLoggedInCustomerUser(array $expectedTreeData): void
    {
        $ownerTree = $this->getFrontendOwnerTreeProvider()->getTree();

        $expectedData = [];
        foreach ($expectedTreeData as $property => $data) {
            $expectedData[$property] = $this->prepareTreeData($data);
        }

        $repository = $this->client->getContainer()->get('doctrine')->getRepository(CustomerUser::class);
        $mainCustomerUser = $repository->findOneBy(['email' => MainLoadCustomerUserData::AUTH_USER]);
        $mainCustomer = $mainCustomerUser->getCustomer();
        $organizationId = $this->getReference(LoadOrganization::ORGANIZATION)->getId();

        $expectedData = array_replace_recursive($expectedData, [
            'userOwningOrganizationId' => [
                $mainCustomerUser->getId() => $organizationId
            ],
            'userOrganizationIds' => [
                $mainCustomerUser->getId() => [$organizationId]
            ],
            'userOwningBusinessUnitId' => [
                $this->getReference(LoadCustomerUserData::RESET_EMAIL)->getId() => $mainCustomer->getId(),
                $this->getReference(LoadCustomerUserData::ANONYMOUS_EMAIL)->getId() => $mainCustomer->getId(),
                $mainCustomerUser->getId() => $mainCustomer->getId()
            ],
            'userBusinessUnitIds' => [
                $this->getReference(LoadCustomerUserData::RESET_EMAIL)->getId() => [$mainCustomer->getId()],
                $this->getReference(LoadCustomerUserData::ANONYMOUS_EMAIL)->getId() => [$mainCustomer->getId()],
                $mainCustomerUser->getId() => [$mainCustomer->getId()]
            ],
            'userOrganizationBusinessUnitIds' => [
                $this->getReference(LoadCustomerUserData::RESET_EMAIL)->getId() => [
                    $organizationId => [$mainCustomer->getId()]
                ],
                $this->getReference(LoadCustomerUserData::ANONYMOUS_EMAIL)->getId() => [
                    $organizationId => [$mainCustomer->getId()]
                ],
                $mainCustomerUser->getId() => [
                    $organizationId => [$mainCustomer->getId()]
                ]
            ],
            'businessUnitOwningOrganizationId' => [
                $mainCustomerUser->getId() => $organizationId
            ],
            'assignedBusinessUnitUserIds' => [
                $mainCustomer->getId() => [
                    $mainCustomerUser->getId(),
                    $this->getReference(LoadCustomerUserData::ANONYMOUS_EMAIL)->getId(),
                    $this->getReference(LoadCustomerUserData::RESET_EMAIL)->getId()
                ]
            ],
        ]);

        $expectedData['organizationBusinessUnitIds'][$organizationId] = array_merge_recursive(
            [$mainCustomer->getId()],
            $expectedData['organizationBusinessUnitIds'][$organizationId]
        );

        $this->assertOwnerTreeEquals($expectedData, $ownerTree);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getTreeNoLoggedInCustomerUserDataProvider(): array
    {
        return [
            'all customers and customer users tree' => [
                'treeData' => [
                    'userOwningOrganizationId' => [
                        LoadCustomerUserData::LEVEL_1_EMAIL => LoadOrganization::ORGANIZATION,
                        LoadCustomerUserData::LEVEL_1_1_EMAIL => LoadOrganization::ORGANIZATION,
                        LoadTreeProviderCustomerUserData::LEVEL_1_2_EMAIL => LoadOrganization::ORGANIZATION,
                        LoadCustomerUserData::GROUP2_EMAIL => LoadOrganization::ORGANIZATION,
                        LoadCustomerUserData::EMAIL => LoadOrganization::ORGANIZATION,
                        LoadCustomerUserData::ORPHAN_EMAIL => LoadOrganization::ORGANIZATION,
                        LoadCustomerUserData::RESET_EMAIL => LoadOrganization::ORGANIZATION,
                        LoadCustomerUserData::ANONYMOUS_EMAIL => LoadOrganization::ORGANIZATION
                    ],
                    'userOrganizationIds' => [
                        LoadCustomerUserData::LEVEL_1_EMAIL => [LoadOrganization::ORGANIZATION],
                        LoadCustomerUserData::LEVEL_1_1_EMAIL => [LoadOrganization::ORGANIZATION],
                        LoadTreeProviderCustomerUserData::LEVEL_1_2_EMAIL => [LoadOrganization::ORGANIZATION],
                        LoadCustomerUserData::GROUP2_EMAIL => [LoadOrganization::ORGANIZATION],
                        LoadCustomerUserData::EMAIL => [LoadOrganization::ORGANIZATION],
                        LoadCustomerUserData::ORPHAN_EMAIL => [LoadOrganization::ORGANIZATION],
                        LoadCustomerUserData::RESET_EMAIL => [LoadOrganization::ORGANIZATION],
                        LoadCustomerUserData::ANONYMOUS_EMAIL => [LoadOrganization::ORGANIZATION]
                    ],
                    'userOwningBusinessUnitId' => [
                        LoadCustomerUserData::LEVEL_1_EMAIL => LoadCustomers::CUSTOMER_LEVEL_1,
                        LoadCustomerUserData::LEVEL_1_1_EMAIL => LoadCustomers::CUSTOMER_LEVEL_1_DOT_1,
                        LoadTreeProviderCustomerUserData::LEVEL_1_2_EMAIL =>
                            LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2,
                        LoadCustomerUserData::GROUP2_EMAIL => LoadCustomers::CUSTOMER_LEVEL_1_DOT_2,
                        LoadCustomerUserData::EMAIL => LoadCustomers::CUSTOMER_LEVEL_1,
                        LoadCustomerUserData::ORPHAN_EMAIL => LoadCustomers::DEFAULT_ACCOUNT_NAME,
                    ],
                    'userBusinessUnitIds' => [
                        LoadCustomerUserData::LEVEL_1_EMAIL => [LoadCustomers::CUSTOMER_LEVEL_1],
                        LoadCustomerUserData::LEVEL_1_1_EMAIL => [LoadCustomers::CUSTOMER_LEVEL_1_DOT_1],
                        LoadTreeProviderCustomerUserData::LEVEL_1_2_EMAIL => [
                            LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2
                        ],
                        LoadCustomerUserData::GROUP2_EMAIL => [LoadCustomers::CUSTOMER_LEVEL_1_DOT_2],
                        LoadCustomerUserData::EMAIL => [LoadCustomers::CUSTOMER_LEVEL_1],
                        LoadCustomerUserData::ORPHAN_EMAIL => [LoadCustomers::DEFAULT_ACCOUNT_NAME],
                    ],
                    'userOrganizationBusinessUnitIds' => [
                        LoadCustomerUserData::LEVEL_1_EMAIL => [
                            LoadOrganization::ORGANIZATION => [LoadCustomers::CUSTOMER_LEVEL_1]
                        ],
                        LoadCustomerUserData::LEVEL_1_1_EMAIL => [
                            LoadOrganization::ORGANIZATION => [LoadCustomers::CUSTOMER_LEVEL_1_DOT_1],
                        ],
                        LoadTreeProviderCustomerUserData::LEVEL_1_2_EMAIL => [
                            LoadOrganization::ORGANIZATION => [LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2],
                        ],
                        LoadCustomerUserData::GROUP2_EMAIL => [
                            LoadOrganization::ORGANIZATION => [LoadCustomers::CUSTOMER_LEVEL_1_DOT_2]
                        ],
                        LoadCustomerUserData::EMAIL => [
                            LoadOrganization::ORGANIZATION => [LoadCustomers::CUSTOMER_LEVEL_1]
                        ],
                        LoadCustomerUserData::ORPHAN_EMAIL => [
                            LoadOrganization::ORGANIZATION => [LoadCustomers::DEFAULT_ACCOUNT_NAME]
                        ],
                    ],
                    'businessUnitOwningOrganizationId' => [
                        LoadCustomers::CUSTOMER_LEVEL_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_2 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_3 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_4 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadCustomers::DEFAULT_ACCOUNT_NAME => LoadOrganization::ORGANIZATION,
                        LoadCustomers::CUSTOMER_LEVEL_1_1 => LoadOrganization::ORGANIZATION,
                        LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2 => LoadOrganization::ORGANIZATION,
                        LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2_DOT_1 => LoadOrganization::ORGANIZATION,
                        LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2_DOT_1_DOT_1 => LoadOrganization::ORGANIZATION,
                    ],
                    'assignedBusinessUnitUserIds' => [
                        LoadCustomers::CUSTOMER_LEVEL_1 => [
                            LoadCustomerUserData::EMAIL,
                            LoadCustomerUserData::LEVEL_1_EMAIL
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_1 => [
                            LoadCustomerUserData::LEVEL_1_1_EMAIL
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2 => [
                            LoadCustomerUserData::GROUP2_EMAIL
                        ],
                        LoadCustomers::DEFAULT_ACCOUNT_NAME => [
                            LoadCustomerUserData::ORPHAN_EMAIL
                        ],
                        LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2 => [
                            LoadTreeProviderCustomerUserData::LEVEL_1_2_EMAIL
                        ]
                    ],
                    'subordinateBusinessUnitIds' => [
                        LoadCustomers::CUSTOMER_LEVEL_1 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_2,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_1 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_2
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_3 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_4 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1
                        ],
                        LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1 => [
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1
                        ],
                        LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2 => [
                            LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2_DOT_1,
                            LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2_DOT_1_DOT_1,
                        ],
                        LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2_DOT_1 => [
                            LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2_DOT_1_DOT_1,
                        ],
                    ],
                    'organizationBusinessUnitIds' => [
                        LoadOrganization::ORGANIZATION => [
                            LoadCustomers::DEFAULT_ACCOUNT_NAME,
                            LoadCustomers::CUSTOMER_LEVEL_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_1_DOT_2,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_2_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_3_DOT_1_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1,
                            LoadCustomers::CUSTOMER_LEVEL_1_DOT_4_DOT_1_DOT_1,
                            LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2,
                            LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2_DOT_1,
                            LoadTreeProviderCustomers::CUSTOMER_LEVEL_1_2_DOT_1_DOT_1
                        ]
                    ],
                ]
            ],
        ];
    }

    /**
     * @dataProvider getTreeDataProvider
     */
    public function testGetTreeByCustomer(array $token, array $expectedTreeData): void
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getReference($token['customerUserReference']);
        $ownerTree = $this->getFrontendOwnerTreeProvider()->getTreeByBusinessUnit($customerUser->getCustomer());

        $expectedData = [];
        foreach ($expectedTreeData as $property => $data) {
            $expectedData[$property] = $this->prepareTreeData($data);
        }

        $this->assertOwnerTreeEquals($expectedData, $ownerTree);
    }

    public function testGetTreeCacheWhenSameTopCustomerUser(): void
    {
        $this->getFrontendOwnerTreeProvider()->clearCache();

        $this->createToken(
            LoadCustomerUserData::LEVEL_1_EMAIL,
            LoadCustomerUserData::LEVEL_1_PASSWORD,
            LoadOrganization::ORGANIZATION
        );

        $this->assertGetTreeQueries(8);

        $this->createToken(
            LoadCustomerUserData::LEVEL_1_1_EMAIL,
            LoadCustomerUserData::LEVEL_1_1_PASSWORD,
            LoadOrganization::ORGANIZATION
        );

        // Only queries for getting customer user and customer data are executed
        $this->assertGetTreeQueries(3);
    }

    public function testGetTreeCacheWhenDifferentTopCustomerUser(): void
    {
        $this->getFrontendOwnerTreeProvider()->clearCache();

        $this->createToken(
            LoadCustomerUserData::LEVEL_1_EMAIL,
            LoadCustomerUserData::LEVEL_1_PASSWORD,
            LoadOrganization::ORGANIZATION
        );

        $this->assertGetTreeQueries(8);

        $this->createToken(
            LoadCustomerUserData::ORPHAN_EMAIL,
            LoadCustomerUserData::ORPHAN_PASSWORD,
            LoadOrganization::ORGANIZATION
        );

        $this->assertGetTreeQueries(5);
    }

    public function testGetTreeCacheWhenNoCustomerUser(): void
    {
        $this->getFrontendOwnerTreeProvider()->clearCache();

        $this->assertGetTreeQueries(2);

        $this->assertGetTreeQueries(2);
    }

    public function testGetTreeCacheByCustomerUser(): void
    {
        $this->getFrontendOwnerTreeProvider()->clearCache();

        $customer = $this->getReference(LoadCustomers::CUSTOMER_LEVEL_1);

        $this->assertGetTreeByCustomerQueries($customer, 8);

        $this->assertGetTreeByCustomerQueries($customer, 1);
    }

    private function assertGetTreeQueries(int $queriesNumber): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $queryTracker = new QueryTracker($entityManager);

        $queryTracker->start();
        $this->getFrontendOwnerTreeProvider()->getTree();
        $queryTracker->stop();

        $this->assertCount($queriesNumber, $queryTracker->getExecutedQueries());
    }

    private function assertGetTreeByCustomerQueries(Customer $customer, int $queriesNumber): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $queryTracker = new QueryTracker($entityManager);

        $queryTracker->start();
        $this->getFrontendOwnerTreeProvider()->getTreeByBusinessUnit($customer);
        $queryTracker->stop();

        $this->assertCount($queriesNumber, $queryTracker->getExecutedQueries());
    }

    private function prepareTreeData(array $treeData): array
    {
        $result = [];
        foreach ($treeData as $key => $value) {
            $keyValue = is_string($key) ? $this->getReference($key)->getId() : $key;

            if (is_string($value)) {
                $result[$keyValue] = $this->getReference($value)->getId();
            } else {
                $result[$keyValue] = $this->prepareTreeData($value);
            }
        }

        return $result;
    }

    private function assertOwnerTreeEquals(array $expected, OwnerTree $actual): void
    {
        $a = new OwnerTreeWrappingPropertiesAccessor($actual);
        self::assertEqualsCanonicalizing(
            $expected['userOwningOrganizationId'],
            $a->xgetUserOwningOrganizationId()
        );
        self::assertEqualsCanonicalizing(
            $expected['userOrganizationIds'],
            $a->xgetUserOrganizationIds()
        );
        self::assertEqualsCanonicalizing(
            $expected['userOwningBusinessUnitId'],
            $a->xgetUserOwningBusinessUnitId()
        );
        self::assertEqualsCanonicalizing(
            $expected['userBusinessUnitIds'],
            $a->xgetUserBusinessUnitIds()
        );
        self::assertEqualsCanonicalizing(
            $expected['userOrganizationBusinessUnitIds'],
            $a->xgetUserOrganizationBusinessUnitIds()
        );
        self::assertEqualsCanonicalizing(
            $expected['businessUnitOwningOrganizationId'],
            $a->xgetBusinessUnitOwningOrganizationId()
        );
        self::assertEqualsCanonicalizing(
            $expected['assignedBusinessUnitUserIds'],
            $a->xgetAssignedBusinessUnitUserIds()
        );
        self::assertEqualsCanonicalizing(
            $expected['subordinateBusinessUnitIds'],
            $a->xgetSubordinateBusinessUnitIds()
        );
        self::assertEqualsCanonicalizing(
            $expected['organizationBusinessUnitIds'],
            $a->xgetOrganizationBusinessUnitIds()
        );
    }

    private function createToken(string $userReference, string $password, string $organizationReference): void
    {
        $token = new UsernamePasswordOrganizationToken(
            $this->getReference($userReference),
            $password,
            'main',
            $this->getReference($organizationReference)
        );

        $this->client->getContainer()->get('security.token_storage')->setToken($token);
    }

    private function getFrontendOwnerTreeProvider(): FrontendOwnerTreeProvider
    {
        return $this->client->getContainer()->get('oro_customer.tests.owner.tree_provider');
    }
}
