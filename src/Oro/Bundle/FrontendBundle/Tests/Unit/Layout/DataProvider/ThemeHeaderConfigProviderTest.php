<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Layout\DataProvider;

use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CMSBundle\Entity\ContentBlock;
use Oro\Bundle\CMSBundle\Entity\Repository\ContentBlockRepository;
use Oro\Bundle\FrontendBundle\Layout\DataProvider\ThemeHeaderConfigProvider;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\FrontendBundle\Provider\QuickAccessButtonDataProvider;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;
use PHPUnit\Framework\TestCase;

final class ThemeHeaderConfigProviderTest extends TestCase
{
    private ThemeHeaderConfigProvider $provider;

    private ThemeConfigurationProvider $themeConfigurationProvider;

    private ManagerRegistry $doctrine;

    private AclHelper $aclHelper;

    private ContentBlockRepository $contentBlockRepository;

    private QuickAccessButtonDataProvider $quickAccessButtonDataProvider;

    protected function setUp(): void
    {
        $this->provider = new ThemeHeaderConfigProvider(
            $this->aclHelper = $this->createMock(AclHelper::class),
            $this->doctrine = $this->createMock(ManagerRegistry::class),
            $this->quickAccessButtonDataProvider = $this->createMock(
                QuickAccessButtonDataProvider::class
            ),
            $this->themeConfigurationProvider = $this->createMock(ThemeConfigurationProvider::class),
        );

        $this->contentBlockRepository = $this->createMock(ContentBlockRepository::class);
    }

    /**
     * @dataProvider contentBlockData
     */
    public function testThatPromotionalBlockAliasReturned(?string $returnedAlias, string $expected): void
    {
        $this->themeConfigurationProvider
            ->expects(self::once())
            ->method('getThemeConfigurationOption')
            ->with('header-promotional_content')
            ->willReturn(1);

        $this->doctrine
            ->expects(self::any())
            ->method('getRepository')
            ->with(ContentBlock::class)
            ->willReturn($this->contentBlockRepository);

        $this->contentBlockRepository
            ->expects(self::once())
            ->method('getContentBlockAliasById')
            ->with(1, $this->aclHelper)
            ->willReturn($returnedAlias);

        self::assertEquals($expected, $this->provider->getPromotionalBlockAlias());
    }

    public function testThatEmptyStringReturnedWhenPromotionalBlockNotSelected(): void
    {
        $this->themeConfigurationProvider
            ->expects(self::once())
            ->method('getThemeConfigurationOption')
            ->with('header-promotional_content')
            ->willReturn(null);

        $this->doctrine
            ->expects(self::never())
            ->method('getRepository');

        self::assertEquals('', $this->provider->getPromotionalBlockAlias());
    }

    /**
     * @dataProvider quickAccessButtonMenuDataProvider
     */
    public function testThatQuickAccessButtonMenuReturned(
        ?QuickAccessButtonConfig $model,
        ?ItemInterface $expected
    ): void {
        $this->themeConfigurationProvider
            ->expects(self::once())
            ->method('getThemeConfigurationOption')
            ->with('header-quick_access_button')
            ->willReturn($model);

        if ($model) {
            $this->quickAccessButtonDataProvider
                ->expects(self::once())
                ->method('getMenu')
                ->with($model)
                ->willReturn($expected);
        }

        self::assertEquals($expected, $this->provider->getQuickAccessButton());
    }

    /**
     * @dataProvider quickAccessButtonLabelDataProvider
     */
    public function testThatQuickAccessButtonLabelReturned(?QuickAccessButtonConfig $model, ?string $expected): void
    {
        $this->themeConfigurationProvider
            ->expects(self::once())
            ->method('getThemeConfigurationOption')
            ->with('header-quick_access_button')
            ->willReturn($model);

        if ($model) {
            $this->quickAccessButtonDataProvider
                ->expects(self::once())
                ->method('getLabel')
                ->with($model)
                ->willReturn($expected);
        }

        self::assertEquals($expected, $this->provider->getQuickAccessButtonLabel());
    }

    private function quickAccessButtonLabelDataProvider(): array
    {
        $model = new QuickAccessButtonConfig();

        return [
            [$model, 'expected'],
            [null, null],
        ];
    }

    private function quickAccessButtonMenuDataProvider(): array
    {
        $model = new QuickAccessButtonConfig();

        $menuItem = $this->createMock(ItemInterface::class);

        return [
            [$model, $menuItem],
            [null, null],
        ];
    }

    private function contentBlockData(): array
    {
        return [
            ['alias', 'alias'],
            [null, '']
        ];
    }
}
