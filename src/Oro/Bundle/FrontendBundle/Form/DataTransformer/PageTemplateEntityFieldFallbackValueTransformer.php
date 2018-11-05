<?php

namespace Oro\Bundle\FrontendBundle\Form\DataTransformer;

use Oro\Bundle\EntityBundle\Entity\EntityFieldFallbackValue;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * The data transformer for fallback fields used to store page templates.
 */
class PageTemplateEntityFieldFallbackValueTransformer implements DataTransformerInterface
{
    /** @var string */
    private $routeName;

    /**
     * @param string $routeName
     */
    public function __construct($routeName)
    {
        $this->routeName = $routeName;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (empty($value)) {
            return null;
        }

        $arrayValue = $value->getArrayValue();
        if ($arrayValue) {
            $value->setScalarValue($arrayValue[$this->routeName]);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof EntityFieldFallbackValue && null !== $value->getScalarValue()) {
            $value->setArrayValue([$this->routeName => $value->getScalarValue()]);
            $value->setScalarValue(null);
        }

        return $value;
    }
}
