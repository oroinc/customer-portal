<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Extension\AdditionalAttrExtension;
use Oro\Bundle\FrontendBundle\Form\Type\PageTemplateCollectionType;
use Oro\Bundle\FrontendBundle\Form\Type\PageTemplateType;
use Oro\Component\Layout\Extension\Theme\Manager\PageTemplatesManager;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class PageTemplateCollectionTypeTest extends FormIntegrationTestCase
{
    /** @var PageTemplatesManager|\PHPUnit_Framework_MockObject_MockObject */
    private $pageTemplatesManagerMock;

    /** @var PageTemplateCollectionType */
    private $formType;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->pageTemplatesManagerMock = $this->getMockBuilder(PageTemplatesManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formType = new PageTemplateCollectionType($this->pageTemplatesManagerMock);
        parent::setUp();
    }

    public function testSubmit()
    {
        $this->pageTemplatesManagerMock->expects($this->exactly(2))
            ->method('getRoutePageTemplates')
            ->willReturn([
                'route_name_1' => [
                    'label' => 'Route title 1',
                    'choices' => [
                        'Page template 1' => 'some_key1',
                        'Page template 2' => 'some_key2',
                    ]
                ]
            ]);

        $form = $this->factory->create(PageTemplateCollectionType::class, []);
        $submittedData = ['route_name_1' => 'some_key2'];

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());

        $formData = $form->getData();
        $this->assertEquals(['route_name_1' => 'some_key2'], $formData);
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        return [
            new PreloadedExtension(
                [
                    PageTemplateCollectionType::class => $this->formType,
                    PageTemplateType::class => new PageTemplateType($this->pageTemplatesManagerMock),
                ],
                [FormType::class => [new AdditionalAttrExtension()]]
            )
        ];
    }
}
