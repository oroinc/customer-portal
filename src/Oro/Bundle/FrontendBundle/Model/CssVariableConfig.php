<?php

namespace Oro\Bundle\FrontendBundle\Model;

/**
 * Represents css variable model with value and variable name
 */
class CssVariableConfig
{
    private string $variableName;

    private ?string $value = null;

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function setVariableName(string $variableName): void
    {
        $this->variableName = $variableName;
    }
}
