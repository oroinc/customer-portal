<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FrontendBundle\Form\Type\PageTemplateCollectionType;
use Oro\Bundle\FrontendBundle\Form\Type\PageTemplateType;
use Oro\Component\Layout\Extension\Theme\Manager\PageTemplatesManager;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\FormView;

class PageTemplateTypeTest extends FormIntegrationTestCase
{
    /** @var PageTemplatesManager|\PHPUnit\Framework\MockObject\MockObject */
    private $pageTemplatesManagerMock;

    /** @var PageTemplateCollectionType */
    private $formType;

    protected function setUp(): void
    {
        $this->pageTemplatesManagerMock = $this->createMock(PageTemplatesManager::class);

        $this->formType = new PageTemplateType($this->pageTemplatesManagerMock);

        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([$this->formType], [])
        ];
    }

    public function testSubmit()
    {
        $this->pageTemplatesManagerMock->expects($this->once())
            ->method('getRoutePageTemplates')
            ->willReturn([
                'route_name_1' => [
                    'label' => 'Route title 1',
                    'choices' => [
                        'some_key1' => 'Page template 1',
                        'some_key2' => 'Page template 2',
                    ]
                ]
            ]);

        $form = $this->factory->create(PageTemplateType::class, null, ['route_name' => 'route_name_1']);
        $submittedData = 'some_key2';

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $formData = $form->getData();
        $this->assertEquals('some_key2', $formData);
    }

    public function testFinishView()
    {
        $this->pageTemplatesManagerMock->expects($this->once())
            ->method('getRoutePageTemplates')
            ->willReturn([
                'route_name_1' => [
                    'choices' => [
                        'some_key1' => 'Page template 1',
                        'some_key2' => 'Page template 2',
                    ],
                    'descriptions' => [
                        'some_key1' => 'Page description 1',
                        'some_key2' => 'Page description 2',
                    ]
                ]
            ]);

        $view = new FormView();
        $form = $this->factory->create(PageTemplateType::class, null, ['route_name' => 'route_name_1']);
        $options = ['route_name' => 'route_name_1'];

        $this->formType->finishView($view, $form, $options);

        $expectedMetadata = [
            'some_key1' => 'Page description 1',
            'some_key2' => 'Page description 2',
        ];

        $this->assertArrayHasKey('page-template-metadata', $view->vars);
        $this->assertEquals($expectedMetadata, $view->vars['page-template-metadata']);
    }
}
