<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\ORM\Walker;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Oro\Bundle\SecurityBundle\AccessRule\AccessRuleExecutor;
use Oro\Bundle\SecurityBundle\ORM\Walker\AccessRuleWalker;
use Oro\Bundle\SecurityBundle\ORM\Walker\AccessRuleWalkerContext;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\Organization;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\ORM\Walker\AclHelper;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AclHelperTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var AclHelper */
    private $helper;

    /** @var ContainerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $container;

    /** @var EntityManager|\PHPUnit\Framework\MockObject\MockObject */
    private $entityManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AccessRuleExecutor|\PHPUnit\Framework\MockObject\MockObject */
    private $accessRuleExecutor;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->entityManager = $this->createMock(EntityManager::class);
        $configuration = $this->createMock(Configuration::class);
        $this->entityManager->expects($this->any())
            ->method('getConfiguration')
            ->willReturn($configuration);
        $configuration->expects($this->any())
            ->method('getDefaultQueryHints')
            ->willReturn([]);

        $this->tokenStorage = new TokenStorage();
        $this->accessRuleExecutor = $this->createMock(AccessRuleExecutor::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->helper = new AclHelper($this->tokenStorage, $this->accessRuleExecutor, $this->websiteManager);
    }

    public function testApplyToQueryWithDefaultConfiguration()
    {
        $query = new Query($this->entityManager);

        $this->websiteManager
            ->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn(null);

        $this->helper->apply($query);
        $hints = $query->getHints();

        $this->assertCount(2, $hints);
        $this->assertEquals([AccessRuleWalker::class], $hints['doctrine.customTreeWalkers']);

        $context = new AccessRuleWalkerContext($this->accessRuleExecutor, 'VIEW', null);
        $this->assertEquals($context, $hints['oro_access_rule.context']);
    }

    public function testApplyToQueryWithWebsite()
    {
        $query = new Query($this->entityManager);

        $organizationId = 2;
        $website = new Website();
        $website->setOrganization(new Organization($organizationId));
        $this->websiteManager
            ->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->helper->apply($query, 'VIEW', ['option1' => true, 'option2' => [3, 2, 1]]);
        $hints = $query->getHints();

        $this->assertCount(2, $hints);
        $this->assertEquals([AccessRuleWalker::class], $hints['doctrine.customTreeWalkers']);

        $context = new AccessRuleWalkerContext($this->accessRuleExecutor, 'VIEW', null, null, $organizationId);
        $context->setOption('option1', true);
        $context->setOption('option2', [3, 2, 1]);
        $this->assertEquals($context, $hints['oro_access_rule.context']);
    }
}
