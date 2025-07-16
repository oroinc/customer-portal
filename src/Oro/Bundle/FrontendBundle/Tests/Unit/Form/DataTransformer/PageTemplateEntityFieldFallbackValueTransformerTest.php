<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\DataTransformer;

use Oro\Bundle\EntityBundle\Entity\EntityFieldFallbackValue;
use Oro\Bundle\FrontendBundle\Form\DataTransformer\PageTemplateEntityFieldFallbackValueTransformer;
use PHPUnit\Framework\TestCase;

class PageTemplateEntityFieldFallbackValueTransformerTest extends TestCase
{
    private PageTemplateEntityFieldFallbackValueTransformer $transformer;

    #[\Override]
    protected function setUp(): void
    {
        $this->transformer = new PageTemplateEntityFieldFallbackValueTransformer('route_name');
    }

    public function testTransform(): void
    {
        $value = new EntityFieldFallbackValue();
        $value->setArrayValue(['route_name' => 'Some value']);
        $this->transformer->transform($value);
        $this->assertEquals('Some value', $value->getScalarValue());
    }

    public function testReverseTransform(): void
    {
        $value = 'value';
        $this->assertEquals($value, $this->transformer->reverseTransform($value));
    }

    public function testReverseTransformEntityFieldFallbackValue(): void
    {
        $value = new EntityFieldFallbackValue();
        $value->setScalarValue('value');

        $this->transformer->reverseTransform($value);

        $this->assertEquals(['route_name' => 'value'], $value->getArrayValue());
        $this->assertNull($value->getScalarValue());
    }

    public function testReverseTransformEntityFieldFallbackValueWhenScalarValueIsNull(): void
    {
        $value = new EntityFieldFallbackValue();

        $this->transformer->reverseTransform($value);

        $this->assertNull($value->getArrayValue());
        $this->assertNull($value->getScalarValue());
    }
}
