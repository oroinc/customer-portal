<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FrontendBundle\Form\Type\PageTemplateFormFieldType;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;

class PageTemplateFormFieldTypeTest extends FormIntegrationTestCase
{
    /** @var PageTemplateFormFieldType */
    private $formType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->formType = new PageTemplateFormFieldType();
    }

    public function testGetName()
    {
        $this->assertEquals(PageTemplateFormFieldType::NAME, $this->formType->getName());
    }
}
