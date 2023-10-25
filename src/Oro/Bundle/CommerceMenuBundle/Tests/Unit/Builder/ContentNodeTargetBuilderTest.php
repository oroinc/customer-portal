<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Builder\ContentNodeTargetBuilder;
use Oro\Bundle\CommerceMenuBundle\Handler\SubFolderUriHandler;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Oro\Bundle\ScopeBundle\Model\ScopeCriteria;
use Oro\Bundle\UIBundle\Tools\UrlHelper;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Provider\RequestWebContentScopeProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UrlHelper as SymfonyUrlHelper;
use Symfony\Component\Routing\RequestContext;

class ContentNodeTargetBuilderTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestWebContentScopeProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $requestWebContentScopeProvider;

    /** @var LocalizationHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $localizationHelper;

    /** @var ScopeManager|\PHPUnit\Framework\MockObject\MockObject */
    private $scopeManager;

    /** @var ContentNodeTargetBuilder */
    private $builder;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->requestWebContentScopeProvider = $this->createMock(RequestWebContentScopeProvider::class);
        $this->scopeManager = $this->createMock(ScopeManager::class);
        $this->localizationHelper = $this->createMock(LocalizationHelper::class);

        $this->builder = new ContentNodeTargetBuilder(
            $this->requestWebContentScopeProvider,
            $this->scopeManager,
            $this->localizationHelper
        );
    }

    public function testBuildWhenNotDisplayed(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem->expects($this->once())
            ->method('isDisplayed')
            ->willReturn(false);
        $menuItem->expects($this->never())
            ->method('setUri');

        $this->builder->build($menuItem);
    }

    public function testBuildWhenNoContentNode(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem->expects($this->once())
            ->method('isDisplayed')
            ->willReturn(true);
        $menuItem->expects($this->once())
            ->method('getChildren')
            ->willReturn([]);
        $menuItem->expects($this->once())
            ->method('getExtra')
            ->with('content_node')
            ->willReturn(null);
        $menuItem->expects($this->never())
            ->method('setUri');

        $this->builder->build($menuItem);
    }

    public function testBuildWhenNoContentNodeScopes(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem->expects($this->once())
            ->method('isDisplayed')
            ->willReturn(true);
        $menuItem->expects($this->once())
            ->method('getChildren')
            ->willReturn([]);
        $menuItem->expects($this->once())
            ->method('getExtra')
            ->with('content_node')
            ->willReturn($contentNode = $this->createMock(ContentNode::class));

        $contentNode->expects($this->once())
            ->method('getScopesConsideringParent')
            ->willReturn($scopes = new ArrayCollection([]));
        $contentNode->expects($this->once())
            ->method('getLocalizedUrls')
            ->willReturn($urls = $this->createMock(Collection::class));

        $this->localizationHelper->expects($this->once())
            ->method('getLocalizedValue')
            ->with($urls)
            ->willReturn($uri = '/sample/uri');

        $menuItem->expects($this->once())
            ->method('setUri')
            ->with($uri);

        $this->builder->build($menuItem);
    }

    public function testBuildWhenNoContentNodeScopesAndHasSubFolder(): void
    {
        $websitePath = '/es';
        $baseUrl = $websitePath.'/index.php';
        $requestStack = $this->createMock(RequestStack::class);
        $requestContext = new RequestContext();
        $urlHelper = new UrlHelper(
            new SymfonyUrlHelper($requestStack),
            $requestStack,
            $requestContext,
        );
        $uriHandler = new SubFolderUriHandler($requestStack, $urlHelper);
        $request = Request::create('', server: ['WEBSITE_PATH' => $websitePath]);
        $requestStack->expects($this->any())
            ->method('getMainRequest')
            ->willReturn($request);
        $requestContext->setBaseUrl($baseUrl);
        $this->builder->setUriHandler($uriHandler);

        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem->expects($this->once())
            ->method('isDisplayed')
            ->willReturn(true);
        $menuItem->expects($this->once())
            ->method('getChildren')
            ->willReturn([]);
        $menuItem->expects($this->once())
            ->method('getExtra')
            ->with('content_node')
            ->willReturn($contentNode = $this->createMock(ContentNode::class));

        $contentNode->expects($this->once())
            ->method('getScopesConsideringParent')
            ->willReturn($scopes = new ArrayCollection([]));
        $contentNode->expects($this->once())
            ->method('getLocalizedUrls')
            ->willReturn($urls = $this->createMock(Collection::class));

        $this->localizationHelper->expects($this->once())
            ->method('getLocalizedValue')
            ->with($urls)
            ->willReturn($uri = '/sample/uri');

        $menuItem->expects($this->once())
            ->method('setUri')
            ->with($baseUrl.$uri);

        $this->builder->build($menuItem);
    }

    public function testBuildWhenNoCurrentScopeCriteria(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem->expects($this->once())
            ->method('isDisplayed')
            ->willReturn(true);
        $menuItem->expects($this->once())
            ->method('getChildren')
            ->willReturn([]);
        $menuItem->expects($this->once())
            ->method('getExtra')
            ->with('content_node')
            ->willReturn($contentNode = $this->createMock(ContentNode::class));

        $contentNode->expects($this->once())
            ->method('getScopesConsideringParent')
            ->willReturn($scopes = new ArrayCollection([$scope = $this->createMock(Scope::class)]));

        $this->requestWebContentScopeProvider->expects($this->once())
            ->method('getScopeCriteria')
            ->willReturn(null);

        $menuItem->expects($this->never())
            ->method('setUri');

        $menuItem->expects($this->once())
            ->method('setDisplay')
            ->with(false);

        $this->builder->build($menuItem);
    }

    public function testBuildWhenScopeMatchesCriteria(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem->expects($this->once())
            ->method('isDisplayed')
            ->willReturn(true);
        $menuItem->expects($this->once())
            ->method('getChildren')
            ->willReturn([]);
        $menuItem->expects($this->once())
            ->method('getExtra')
            ->with('content_node')
            ->willReturn($contentNode = $this->createMock(ContentNode::class));

        $contentNode->expects($this->once())
            ->method('getScopesConsideringParent')
            ->willReturn($scopes = new ArrayCollection([$scope = $this->createMock(Scope::class)]));

        $this->requestWebContentScopeProvider->expects($this->once())
            ->method('getScopeCriteria')
            ->willReturn($currentScopeCriteria = $this->createMock(ScopeCriteria::class));

        $this->scopeManager->expects($this->once())
            ->method('isScopeMatchCriteria')
            ->with($scope, $currentScopeCriteria, 'web_content')
            ->willReturn(true);

        $contentNode->expects($this->once())
            ->method('getLocalizedUrls')
            ->willReturn($urls = $this->createMock(Collection::class));

        $this->localizationHelper->expects($this->once())
            ->method('getLocalizedValue')
            ->with($urls)
            ->willReturn($uri = '/sample/uri');

        $menuItem->expects($this->once())
            ->method('setUri')
            ->with($uri);

        $this->builder->build($menuItem);
    }

    public function testBuildWhenScopeNotMatchesCriteria(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem->expects($this->once())
            ->method('isDisplayed')
            ->willReturn(true);

        $menuItem->expects($this->once())
            ->method('getChildren')
            ->willReturn([]);

        $menuItem->expects($this->once())
            ->method('getExtra')
            ->with('content_node')
            ->willReturn($contentNode = $this->createMock(ContentNode::class));

        $contentNode->expects($this->once())
            ->method('getScopesConsideringParent')
            ->willReturn($scopes = new ArrayCollection([$scope = $this->createMock(Scope::class)]));

        $this->requestWebContentScopeProvider->expects($this->once())
            ->method('getScopeCriteria')
            ->willReturn($currentScopeCriteria = $this->createMock(ScopeCriteria::class));

        $this->scopeManager->expects($this->once())
            ->method('isScopeMatchCriteria')
            ->with($scope, $currentScopeCriteria, 'web_content')
            ->willReturn(false);

        $menuItem->expects($this->never())
            ->method('setUri');

        $menuItem->expects($this->once())
            ->method('setDisplay')
            ->with(false);

        $this->builder->build($menuItem);
    }

    public function testBuildChildWhenScopeMatchesCriteria(): void
    {
        $parentMenuItem = $this->createMock(ItemInterface::class);
        $parentMenuItem->expects($this->once())
            ->method('isDisplayed')
            ->willReturn(true);
        $parentMenuItem->expects($this->once())
            ->method('getChildren')
            ->willReturn([$menuItem = $this->createMock(ItemInterface::class)]);
        $parentMenuItem->expects($this->once())
            ->method('getExtra')
            ->with('content_node')
            ->willReturn(null);

        $menuItem->expects($this->once())
            ->method('isDisplayed')
            ->willReturn(true);
        $menuItem->expects($this->once())
            ->method('getChildren')
            ->willReturn([]);
        $menuItem->expects($this->once())
            ->method('getExtra')
            ->with('content_node')
            ->willReturn($contentNode = $this->createMock(ContentNode::class));

        $contentNode->expects($this->once())
            ->method('getScopesConsideringParent')
            ->willReturn($scopes = new ArrayCollection([$scope = $this->createMock(Scope::class)]));

        $this->requestWebContentScopeProvider->expects($this->once())
            ->method('getScopeCriteria')
            ->willReturn($currentScopeCriteria = $this->createMock(ScopeCriteria::class));

        $this->scopeManager->expects($this->once())
            ->method('isScopeMatchCriteria')
            ->with($scope, $currentScopeCriteria, 'web_content')
            ->willReturn(true);

        $contentNode->expects($this->once())
            ->method('getLocalizedUrls')
            ->willReturn($urls = $this->createMock(Collection::class));

        $this->localizationHelper->expects($this->once())
            ->method('getLocalizedValue')
            ->with($urls)
            ->willReturn($uri = '/sample/uri');

        $menuItem->expects($this->once())
            ->method('setUri')
            ->with($uri);

        $this->builder->build($parentMenuItem);
    }
}
