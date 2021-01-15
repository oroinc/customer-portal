<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product;
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

    const WEBSITE_ID = 42;

    /**
     * @var WebsiteScopedDataType
     */
    protected $formType;

    /**
     * {@inheritdoc}
     */
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
        $websiteQB = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResult'])
            ->getMock();
        $websiteQB
            ->expects($this->any())
            ->method('getResult')
            ->willReturn($websites);

        $websiteRepository = $this->createMock(WebsiteRepository::class);
        $websiteRepository->expects($this->any())
            ->method('createQueryBuilder')
            ->with('website')
            ->willReturn($websiteQB);

        /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject $registry*/
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
            ->willReturn($websiteQB);

        $this->formType = new WebsiteScopedDataType($registry, $aclHelper);
        $this->formType->setWebsiteClass(Website::class);
        parent::setUp();
    }

    /**
     * @dataProvider submitDataProvider
     * @param Product $defaultData
     * @param array $options
     * @param array $submittedData
     * @param array $expectedData
     */
    public function testSubmit($defaultData, array $options, array $submittedData, array $expectedData)
    {
        $form = $this->factory->create(WebsiteScopedDataType::class, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());
        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $data = $form->getData();

        $this->assertEquals($expectedData, $data);
    }

    /**
     * @return array
     */
    public function submitDataProvider()
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

        /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject $form */
        $form = $this->createMock('Symfony\Component\Form\FormInterface');
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

    /**
     * @return array
     */
    public function finishViewDataProvider()
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

    /**
     * @param FormView $formView
     * @param array $children
     * @return FormView
     */
    protected function setFormViewChildren(FormView $formView, array $children)
    {
        $childrenReflection = new \ReflectionProperty($formView, 'children');
        $childrenReflection->setAccessible(true);
        $childrenReflection->setValue($formView, $children);

        return $formView;
    }
}
