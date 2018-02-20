<?php

namespace Oro\Bundle\FrontendBundle\Form\DataTransformer;

use Oro\Bundle\EntityBundle\Entity\EntityFieldFallbackValue;
use Symfony\Component\Form\DataTransformerInterface;

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
            return;
        }

        $arrValue = $value->getArrayValue();

        if ($arrValue) {
            $value->setScalarValue($arrValue[$this->routeName]);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (empty($value)) {
            return;
        }

        if ($value instanceof EntityFieldFallbackValue) {
            $value->setArrayValue([$this->routeName => $value->getScalarValue()]);
            $value->setScalarValue(null);
        }

        return $value;
    }
}
