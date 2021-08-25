<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\CustomerBundle\Acl\Voter\CustomerUserRoleVoter;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerUserRoleVoterTest extends \PHPUnit\Framework\TestCase
{
    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /** @var CustomerUserRoleVoter */
    private $voter;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->voter = new CustomerUserRoleVoter($this->doctrineHelper, $this->authorizationChecker);
    }

    /**
     * @dataProvider attributesDataProvider
     */
    public function testVoteAttribute(string $attribute, bool $isCustomerGranted, bool $withCustomer, int $expected)
    {
        $token = $this->createMock(TokenInterface::class);

        $object = new CustomerUserRole('');

        $customer = new Customer();
        if ($withCustomer) {
            $object->setCustomer($customer);
        }

        $this->doctrineHelper->expects($this->any())
            ->method('getSingleEntityIdentifier')
            ->with($object, false)
            ->willReturn(1);

        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->with(CustomerUserRoleVoter::ATTRIBUTE_VIEW, $customer)
            ->willReturn($isCustomerGranted);

        $this->voter->setClassName(get_class($object));
        $this->assertEquals(
            $expected,
            $this->voter->vote($token, $object, [$attribute])
        );
    }

    public function attributesDataProvider(): array
    {
        return [
            'VIEW common role'                     => [
                'attribute'         => CustomerUserRoleVoter::ATTRIBUTE_VIEW,
                'isCustomerGranted' => false,
                'withCustomer'      => false,
                'expected'          => VoterInterface::ACCESS_GRANTED,
            ],
            'VIEW common role allow customer'      => [
                'attribute'         => CustomerUserRoleVoter::ATTRIBUTE_VIEW,
                'isCustomerGranted' => true,
                'withCustomer'      => true,
                'expected'          => VoterInterface::ACCESS_GRANTED,
            ],
            'VIEW common role disallow customer'   => [
                'attribute'         => CustomerUserRoleVoter::ATTRIBUTE_VIEW,
                'isCustomerGranted' => false,
                'withCustomer'      => true,
                'expected'          => VoterInterface::ACCESS_DENIED,
            ],
            'EDIT common role'                     => [
                'attribute'         => CustomerUserRoleVoter::ATTRIBUTE_EDIT,
                'isCustomerGranted' => false,
                'withCustomer'      => false,
                'expected'          => VoterInterface::ACCESS_GRANTED,
            ],
            'EDIT common role allow customer'      => [
                'attribute'         => CustomerUserRoleVoter::ATTRIBUTE_EDIT,
                'isCustomerGranted' => true,
                'withCustomer'      => true,
                'expected'          => VoterInterface::ACCESS_GRANTED,
            ],
            'EDIT common role disallow customer'   => [
                'attribute'         => CustomerUserRoleVoter::ATTRIBUTE_EDIT,
                'isCustomerGranted' => false,
                'withCustomer'      => true,
                'expected'          => VoterInterface::ACCESS_DENIED,
            ],
            'ASSIGN common role'                   => [
                'attribute'         => CustomerUserRoleVoter::ATTRIBUTE_ASSIGN,
                'isCustomerGranted' => false,
                'withCustomer'      => false,
                'expected'          => VoterInterface::ACCESS_GRANTED,
            ],
            'ASSIGN common role allow customer'    => [
                'attribute'         => CustomerUserRoleVoter::ATTRIBUTE_ASSIGN,
                'isCustomerGranted' => true,
                'withCustomer'      => true,
                'expected'          => VoterInterface::ACCESS_GRANTED,
            ],
            'ASSIGN common role disallow customer' => [
                'attribute'         => CustomerUserRoleVoter::ATTRIBUTE_ASSIGN,
                'isCustomerGranted' => false,
                'withCustomer'      => true,
                'expected'          => VoterInterface::ACCESS_DENIED,
            ],
        ];
    }

    /**
     * @dataProvider attributeDeleteDataProvider
     */
    public function testVoteDelete(
        bool $isDefaultWebsiteRole,
        bool $hasUsers,
        bool $isCustomerGranted,
        bool $withCustomer,
        int $expected
    ) {
        $token = $this->createMock(TokenInterface::class);

        $object = new CustomerUserRole('');

        $customer = new Customer();
        if ($withCustomer) {
            $object->setCustomer($customer);
        }

        $this->doctrineHelper->expects($this->any())
            ->method('getSingleEntityIdentifier')
            ->with($object, false)
            ->willReturn(1);

        $entityRepository = $this->createMock(CustomerUserRoleRepository::class);
        $entityRepository->expects($this->any())
            ->method('isDefaultOrGuestForWebsite')
            ->willReturn($isDefaultWebsiteRole);
        $entityRepository->expects($this->any())
            ->method('hasAssignedUsers')
            ->willReturn($hasUsers);

        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->with(CustomerUserRoleVoter::ATTRIBUTE_VIEW, $customer)
            ->willReturn($isCustomerGranted);

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->with(CustomerUserRole::class)
            ->willReturn($entityRepository);

        $this->voter->setClassName(get_class($object));
        $this->assertEquals(
            $expected,
            $this->voter->vote($token, $object, [CustomerUserRoleVoter::ATTRIBUTE_DELETE])
        );
    }

    public function attributeDeleteDataProvider(): array
    {
        return [
            'common role'                   => [
                'isDefaultWebsiteRole' => false,
                'hasUsers'             => false,
                'isCustomerGranted'    => false,
                'withCustomer'         => false,
                'expected'             => VoterInterface::ACCESS_GRANTED,
            ],
            'common role allow customer'    => [
                'isDefaultWebsiteRole' => false,
                'hasUsers'             => false,
                'isCustomerGranted'    => true,
                'withCustomer'         => true,
                'expected'             => VoterInterface::ACCESS_GRANTED,
            ],
            'common role disallow customer' => [
                'isDefaultWebsiteRole' => false,
                'hasUsers'             => false,
                'isCustomerGranted'    => false,
                'withCustomer'         => true,
                'expected'             => VoterInterface::ACCESS_DENIED,
            ],
            'default website role'          => [
                'isDefaultWebsiteRole' => true,
                'hasUsers'             => false,
                'isCustomerGranted'    => false,
                'withCustomer'         => false,
                'expected'             => VoterInterface::ACCESS_DENIED,
            ],
            'role wit users'                => [
                'isDefaultWebsiteRole' => false,
                'hasUsers'             => true,
                'isCustomerGranted'    => false,
                'withCustomer'         => false,
                'expected'             => VoterInterface::ACCESS_DENIED,
            ],
        ];
    }

    /**
     * @dataProvider attributeFrontendUpdateViewDataProvider
     */
    public function testVoteFrontendUpdate(
        ?CustomerUser $customerUser,
        bool $isGranted,
        int $customerId,
        int $loggedUserCustomerId,
        int $expected,
        bool $failCustomerUserRole = false
    ) {
        $token = $this->createMock(TokenInterface::class);

        $roleCustomer = $this->getCustomer($customerId);
        $userCustomer = $this->getCustomer($loggedUserCustomerId);

        if ($failCustomerUserRole) {
            $customerUserRole = new \stdClass();
        } else {
            $customerUserRole = new CustomerUserRole('');
            $customerUserRole->setCustomer($roleCustomer);
        }

        if ($customerUser) {
            $customerUser->setCustomer($userCustomer);
        }

        $this->doctrineHelper->expects($this->any())
            ->method('getSingleEntityIdentifier')
            ->with($customerUserRole, false)
            ->willReturn(1);

        if (!$failCustomerUserRole) {
            $token->expects($this->once())
                ->method('getUser')
                ->willReturn($customerUser);
            $this->authorizationChecker->expects($customerUser ? $this->once() : $this->never())
                ->method('isGranted')
                ->with('EDIT', $customerUserRole)
                ->willReturn($isGranted);
        }

        $this->voter->setClassName(get_class($customerUserRole));
        $this->assertEquals(
            $expected,
            $this->voter->vote(
                $token,
                $customerUserRole,
                [CustomerUserRoleVoter::ATTRIBUTE_FRONTEND_CUSTOMER_ROLE_UPDATE]
            )
        );
    }

    /**
     * @dataProvider attributeFrontendUpdateViewDataProvider
     */
    public function testVoteFrontendView(
        ?CustomerUser $customerUser,
        bool $isGranted,
        int $customerId,
        int $loggedUserCustomerId,
        int $expected,
        bool $failCustomerUserRole = false
    ) {
        $token = $this->createMock(TokenInterface::class);

        $roleCustomer = $this->getCustomer($customerId);
        $userCustomer = $this->getCustomer($loggedUserCustomerId);

        if ($failCustomerUserRole) {
            $customerUserRole = new \stdClass();
        } else {
            $customerUserRole = new CustomerUserRole('');
            $customerUserRole->setCustomer($roleCustomer);
        }

        if ($customerUser) {
            $customerUser->setCustomer($userCustomer);
        }

        $this->doctrineHelper->expects($this->any())
            ->method('getSingleEntityIdentifier')
            ->with($customerUserRole, false)
            ->willReturn(1);

        if (!$failCustomerUserRole) {
            $token->expects($this->once())
                ->method('getUser')
                ->willReturn($customerUser);

            $this->authorizationChecker->expects($customerUser ? $this->once() : $this->never())
                ->method('isGranted')
                ->with('VIEW', $customerUserRole)
                ->willReturn($isGranted);
        }

        $this->voter->setClassName(get_class($customerUserRole));
        $this->assertEquals(
            $expected,
            $this->voter->vote(
                $token,
                $customerUserRole,
                [CustomerUserRoleVoter::ATTRIBUTE_FRONTEND_CUSTOMER_ROLE_VIEW]
            )
        );
    }

    public function attributeFrontendUpdateViewDataProvider(): array
    {
        $customerUser = new CustomerUser();

        return [
            'customer with logged user the same' => [
                'customerUser'         => $customerUser,
                'isGranted'            => true,
                'customerId'           => 1,
                'loggedUserCustomerId' => 1,
                'expected'             => VoterInterface::ACCESS_GRANTED,
            ],
            'isGranted false'                    => [
                'customerUser'         => $customerUser,
                'isGranted'            => false,
                'customerId'           => 1,
                'loggedUserCustomerId' => 1,
                'expected'             => VoterInterface::ACCESS_DENIED,
            ],
            'without customerUser'               => [
                'customerUser'         => null,
                'isGranted'            => false,
                'customerId'           => 1,
                'loggedUserCustomerId' => 1,
                'expected'             => VoterInterface::ACCESS_DENIED,
            ],
            'without customerUserRole'           => [
                'customerUser'         => null,
                'isGranted'            => false,
                'customerId'           => 1,
                'loggedUserCustomerId' => 1,
                'expected'             => VoterInterface::ACCESS_ABSTAIN,
                'failCustomerUserRole' => true,
            ],
        ];
    }

    private function getCustomer(int $id = null): Customer
    {
        $entity = new Customer();
        ReflectionUtil::setId($entity, $id);

        return $entity;
    }
}
