<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\CustomerBundle\Acl\Cache\CustomerVisitorAclCache;
use Oro\Bundle\CustomerBundle\Acl\Voter\CustomerVisitorAclVoterDecorator;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\CustomerBundle\Tests\Unit\Fixtures\CustomerVisitorOwnedEntity;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\Acl\Domain\DomainObjectWrapper;
use Oro\Bundle\SecurityBundle\Acl\Voter\AclVoterInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Provider\RequestWebsiteProvider;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

class CustomerVisitorAclVoterDecoratorTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var \PHPUnit\Framework\MockObject\MockObject|AclVoterInterface */
    private $innerVoter;

    /** @var \PHPUnit\Framework\MockObject\MockObject|CustomerVisitorAclCache */
    private $visitorAclCache;

    /** @var \PHPUnit\Framework\MockObject\MockObject|RequestWebsiteProvider */
    private $websiteProvider;

    /** @var CustomerVisitorAclVoterDecorator */
    private $aclVoterDecorator;

    #[\Override]
    protected function setUp(): void
    {
        $this->innerVoter = $this->createMock(AclVoterInterface::class);
        $this->visitorAclCache = $this->createMock(CustomerVisitorAclCache::class);
        $this->websiteProvider = $this->createMock(RequestWebsiteProvider::class);

        $this->aclVoterDecorator = new CustomerVisitorAclVoterDecorator(
            $this->innerVoter,
            $this->visitorAclCache,
            $this->websiteProvider
        );
    }

    public function testVoteOnNotVisitorToken(): void
    {
        $token = new UsernamePasswordToken($this->createMock(UserInterface::class), 'main', []);
        $subject = new Product();
        $attributes = ['VIEW'];

        $this->websiteProvider->expects(self::never())
            ->method('getWebsite');

        $this->visitorAclCache->expects(self::never())
            ->method('isVoteResultExist');

        $this->visitorAclCache->expects(self::never())
            ->method('getVoteResult');

        $this->visitorAclCache->expects(self::never())
            ->method('cacheAclResult');

        $this->innerVoter->expects(self::once())
            ->method('vote')
            ->with($token, $subject, $attributes)
            ->willReturn(1);

        self::assertEquals(1, $this->aclVoterDecorator->vote($token, $subject, $attributes));
    }

    public function testVoteOnCustomerVisitorOwnerAwareInterfaceSubject(): void
    {
        $token = new AnonymousCustomerUserToken(new CustomerVisitor());
        $subject = new CustomerVisitorOwnedEntity();
        $attributes = ['VIEW'];

        $this->websiteProvider->expects(self::never())
            ->method('getWebsite');

        $this->visitorAclCache->expects(self::never())
            ->method('isVoteResultExist');

        $this->visitorAclCache->expects(self::never())
            ->method('getVoteResult');

        $this->visitorAclCache->expects(self::never())
            ->method('cacheAclResult');

        $this->innerVoter->expects(self::once())
            ->method('vote')
            ->with($token, $subject, $attributes)
            ->willReturn(1);

        self::assertEquals(1, $this->aclVoterDecorator->vote($token, $subject, $attributes));
    }

    public function testVoteOnCachedStringSubject(): void
    {
        $website = $this->getEntity(Website::class, ['id' => 10]);
        $token = new AnonymousCustomerUserToken(new CustomerVisitor());
        $subject = 'some_action';
        $attributes = ['VIEW'];

        $this->websiteProvider->expects(self::once())
            ->method('getWebsite')
            ->willReturn($website);

        $this->visitorAclCache->expects(self::once())
            ->method('isVoteResultExist')
            ->with(10, $subject, $attributes)
            ->willReturn(true);

        $this->visitorAclCache->expects(self::once())
            ->method('getVoteResult')
            ->with(10, $subject, $attributes)
            ->willReturn(0);

        $this->visitorAclCache->expects(self::never())
            ->method('cacheAclResult');

        $this->innerVoter->expects(self::never())
            ->method('vote');

        self::assertEquals(0, $this->aclVoterDecorator->vote($token, $subject, $attributes));
    }

    public function testVoteOnNonCachedStringSubject(): void
    {
        $website = $this->getEntity(Website::class, ['id' => 11]);
        $token = new AnonymousCustomerUserToken(new CustomerVisitor());
        $subject = 'some_action';
        $attributes = ['VIEW'];

        $this->websiteProvider->expects(self::once())
            ->method('getWebsite')
            ->willReturn($website);

        $this->visitorAclCache->expects(self::once())
            ->method('isVoteResultExist')
            ->with(11, $subject, $attributes)
            ->willReturn(false);

        $this->visitorAclCache->expects(self::never())
            ->method('getVoteResult');

        $this->visitorAclCache->expects(self::once())
            ->method('cacheAclResult')
            ->with(11, $subject, $attributes, 0);

        $this->innerVoter->expects(self::once())
            ->method('vote')
            ->with($token, $subject, $attributes)
            ->willReturn(0);

        self::assertEquals(0, $this->aclVoterDecorator->vote($token, $subject, $attributes));
    }

    /**
     * @dataProvider subjectsProvider
     */
    public function testVoteSubjects($subject, $expectedSubjectName): void
    {
        $website = $this->getEntity(Website::class, ['id' => 12]);
        $token = new AnonymousCustomerUserToken(new CustomerVisitor());
        $attributes = ['VIEW'];

        $this->websiteProvider->expects(self::once())
            ->method('getWebsite')
            ->willReturn($website);

        $this->visitorAclCache->expects(self::once())
            ->method('isVoteResultExist')
            ->with(12, $expectedSubjectName, $attributes)
            ->willReturn(true);

        $this->visitorAclCache->expects(self::once())
            ->method('getVoteResult')
            ->with(12, $expectedSubjectName, $attributes)
            ->willReturn(-1);

        $this->visitorAclCache->expects(self::never())
            ->method('cacheAclResult');

        $this->innerVoter->expects(self::never())
            ->method('vote');

        self::assertEquals(-1, $this->aclVoterDecorator->vote($token, $subject, $attributes));
    }

    public function subjectsProvider(): array
    {
        return [
            'string' => ['some_action', 'some_action'],
            'string with group' => ['commerce@test_action', 'test_action'],
            'field vote object' => [new FieldVote(new Product(), 'field1'), Product::class],
            'domain wrapper object' => [
                new DomainObjectWrapper(new Product(), new ObjectIdentity('entity', Product::class)),
                Product::class
            ],
            'object identity object' => [new ObjectIdentity('entity', Product::class), Product::class],
            'entity object' => [new Product(), Product::class]
        ];
    }
}
