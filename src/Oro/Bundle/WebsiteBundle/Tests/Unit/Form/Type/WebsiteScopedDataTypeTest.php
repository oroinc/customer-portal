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
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class WebsiteScopedDataTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    private const WEBSITE_ID = 42;

    /** @var WebsiteScopedDataType */
    private $formType;

    protected function getExtensions()
    {
        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    StubType::class => new StubType(),
                ],
                []
            )
        ];
    }

    protected function setUp(): void
    {
        $em = $this->createMock(EntityManager::class);
        $em->expects($this->any())
            ->method('getReference')
            ->with(Website::class, self::WEBSITE_ID)
            ->willReturn($this->getEntity(Website::class, ['id' => self::WEBSITE_ID]));

        $websites = [self::WEBSITE_ID => $this->getEntity(Website::class, ['id' => self::WEBSITE_ID])];
        $websiteQuery = $this->createMock(AbstractQuery::class);
        $websiteQB = $this->createMock(QueryBuilder::class);
        $websiteQB->expects($this->any())
            ->method('getQuery')
            ->willReturn($websiteQuery);
        $websiteQuery->expects($this->any())
            ->method('getResult')
            ->willReturn($websites);

        $websiteRepository = $this->createMock(WebsiteRepository::class);
        $websiteRepository->expects($this->any())
            ->method('createQueryBuilder')
            ->with('website')
            ->willReturn($websiteQB);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->any())
            ->method('getRepository')
            ->with(Website::class)
            ->willReturn($websiteRepository);
        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->with(Website::class)
            ->willReturn($em);

        $aclHelper = $this->createMock(AclHelper::class);
        $aclHelper->expects($this->any())
            ->method('apply')
            ->willReturn($websiteQuery);

        $this->formType = new WebsiteScopedDataType($registry, $aclHelper);
        $this->formType->setWebsiteClass(Website::class);
        parent::setUp();
    }

    /**
     * @dataProvider submitDataProvider
     */
    public function testSubmit(array $defaultData, array $options, array $submittedData, array $expectedData)
    {
        $form = $this->factory->create(WebsiteScopedDataType::class, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());
        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $data = $form->getData();

        $this->assertEquals($expectedData, $data);
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

    public function testBuildView()
    {
        $view = new FormView();

        $form = $this->createMock(FormInterface::class);
        $this->formType->buildView($view, $form, ['region_route' => 'test']);

        $this->assertArrayHasKey('websites', $view->vars);

        $websiteIds = array_map(
            function (Website $website) {
                return $website->getId();
            },
            $view->vars['websites']
        );

        $this->assertEquals([self::WEBSITE_ID => self::WEBSITE_ID], $websiteIds);
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
