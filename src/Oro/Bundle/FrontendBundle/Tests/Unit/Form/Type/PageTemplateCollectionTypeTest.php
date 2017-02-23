<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Extension\AdditionalAttrExtension;
use Oro\Bundle\FrontendBundle\Form\Type\PageTemplateCollectionType;
use Oro\Bundle\FrontendBundle\Form\Type\PageTemplateType;
use Oro\Component\Layout\Extension\Theme\Manager\PageTemplatesManager;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Symfony\Component\Form\PreloadedExtension;

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

        parent::setUp();

        $this->formType = new PageTemplateCollectionType($this->pageTemplatesManagerMock);
    }

    public function testGetName()
    {
        $this->assertEquals(PageTemplateCollectionType::NAME, $this->formType->getName());
    }

    public function testSubmit()
    {
        $this->pageTemplatesManagerMock->expects($this->exactly(2))
            ->method('getRoutePageTemplates')
            ->willReturn([
                'route_name_1' => [
                    'label' => 'Route title 1',
                    'choices' => [
                        'some_key1' => 'Page template 1',
                        'some_key2' => 'Page template 2'
                    ]
                ]
            ]);

        $form = $this->factory->create($this->formType, []);
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
                    PageTemplateType::class => new PageTemplateType($this->pageTemplatesManagerMock),
                ],
                ['form' => [new AdditionalAttrExtension()]]
            )
        ];
    }
}
