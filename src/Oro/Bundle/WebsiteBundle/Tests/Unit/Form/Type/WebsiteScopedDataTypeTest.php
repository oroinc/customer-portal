<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Form\Type\WebsiteScopedDataType;
use Oro\Bundle\WebsiteBundle\Tests\Unit\Form\Type\Stub\StubType;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class WebsiteScopedDataTypeTest extends FormIntegrationTestCase
{
    private const WEBSITE_ID = 42;

    private WebsiteScopedDataType $formType;

    protected function setUp(): void
    {
        $em = $this->createMock(EntityManager::class);
        $em->expects($this->any())
            ->method('getReference')
            ->with(Website::class, self::WEBSITE_ID)
            ->willReturn($this->getWebsite(self::WEBSITE_ID));

        $websiteQuery = $this->createMock(AbstractQuery::class);
        $websiteQB = $this->createMock(QueryBuilder::class);
        $websiteQB->expects($this->any())
            ->method('getQuery')
            ->willReturn($websiteQuery);
        $websiteQuery->expects($this->any())
            ->method('getResult')
            ->willReturn([self::WEBSITE_ID => $this->getWebsite(self::WEBSITE_ID)]);

        $websiteRepository = $this->createMock(WebsiteRepository::class);
        $websiteRepository->expects($this->any())
            ->method('createQueryBuilder')
            ->with('website')
            ->willReturn($websiteQB);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->any())
            ->method('getRepository')
            ->with(Website::class)
            ->willReturn($websiteRepository);
        $doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->with(Website::class)
            ->willReturn($em);

        $aclHelper = $this->createMock(AclHelper::class);
        $aclHelper->expects($this->any())
            ->method('apply')
            ->willReturn($websiteQuery);

        $this->formType = new WebsiteScopedDataType($doctrine, $aclHelper);

        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        return [new PreloadedExtension([$this->formType, new StubType()], [])];
    }

    private function getWebsite(int $id): Website
    {
        $website = new Website();
        ReflectionUtil::setId($website, $id);

        return $website;
    }

    /**
     * @dataProvider submitDataProvider
     */
    public function testSubmit(array $defaultData, array $options, array $submittedData, array $expectedData): void
    {
        $form = $this->factory->create(WebsiteScopedDataType::class, $defaultData, $options);

        self::assertEquals($defaultData, $form->getData());
        $form->submit($submittedData);
        self::assertTrue($form->isValid());
        self::assertTrue($form->isSynchronized());

        $data = $form->getData();

        self::assertEquals($expectedData, $data);
    }

    public function submitDataProvider(): array
    {
        return [
            [
                'defaultData'   => [],
                'options' => [
                    'preloaded_websites' => [],
                    'type' => StubType::class
                ],
                'submittedData' => [
                    self::WEBSITE_ID => [],
                ],
                'expectedData'  => [
                    self::WEBSITE_ID => [],
                ],
            ],
        ];
    }

    public function testBuildView(): void
    {
        $view = new FormView();

        $form = $this->createMock(FormInterface::class);
        $this->formType->buildView($view, $form, ['region_route' => 'test']);

        self::assertArrayHasKey('websites', $view->vars);

        $websiteIds = array_map(
            function (Website $website) {
                return $website->getId();
            },
            $view->vars['websites']
        );

        self::assertEquals([self::WEBSITE_ID => self::WEBSITE_ID], $websiteIds);
    }

    public function finishViewDataProvider(): array
    {
        return [
            [
                'children' => ['1' => 'test'],
                'expected' => []
            ],
            [
                'children' => ['1' => 'test', 'not_int' => 'test'],
                'expected' => ['not_int' => 'test']
            ],
            [
                'children' => ['1' => 'test', 'not_int' => 'test'],
                'expected' => ['1' => 'test', 'not_int' => 'test']
            ],
        ];
    }
}
