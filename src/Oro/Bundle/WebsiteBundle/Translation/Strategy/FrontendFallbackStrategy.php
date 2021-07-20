<?php

namespace Oro\Bundle\WebsiteBundle\Translation\Strategy;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\TranslationBundle\Strategy\TranslationStrategyInterface;

class FrontendFallbackStrategy implements TranslationStrategyInterface
{
    /**
     * @var FrontendHelper
     */
    protected $frontendHelper;

    /**
     * @var TranslationStrategyInterface
     */
    protected $strategy;

    public function __construct(FrontendHelper $frontendHelper, TranslationStrategyInterface $strategy)
    {
        $this->frontendHelper = $frontendHelper;
        $this->strategy = $strategy;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->strategy->getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getLocaleFallbacks()
    {
        return $this->strategy->getLocaleFallbacks();
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable()
    {
        return $this->frontendHelper->isFrontendRequest();
    }
}
