<?php

namespace Oro\Bundle\AddressValidationBundle\Model;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;

/**
 * Represent state of form AddressValidationForm
 */
class AddressValidationModel
{
    public const string SUGGESTION_TYPE_ENTERED = 'entered';

    public const string SUGGESTION_TYPE_SUGGESTED = 'suggested';

    private ?AbstractAddress $suggestedAddress = null;

    private ?string $suggestionType = null;

    public function __construct(private readonly AbstractAddress $enteredAddress)
    {
    }

    public function getEnteredAddress(): AbstractAddress
    {
        return $this->enteredAddress;
    }

    public function getSuggestionType(): ?string
    {
        return $this->suggestionType;
    }

    public function setSuggestionType(?string $suggestionType): void
    {
        $this->suggestionType = $suggestionType;
    }

    public function getSuggestedAddress(): ?AbstractAddress
    {
        return $this->suggestedAddress;
    }

    public function setSuggestedAddress(AbstractAddress $suggestedAddress): void
    {
        $this->suggestedAddress = $suggestedAddress;
    }

    public function isSuggestedAddressSelected(): bool
    {
        return !$this->getSuggestionType() || $this->getSuggestionType() === self::SUGGESTION_TYPE_SUGGESTED;
    }
}
