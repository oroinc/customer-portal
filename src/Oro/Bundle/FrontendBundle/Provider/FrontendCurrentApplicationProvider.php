<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Oro\Bundle\ActionBundle\Provider\CurrentApplicationProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * The implementation of the current application provider
 * that determines storefront request as "commerce" application.
 */
class FrontendCurrentApplicationProvider extends CurrentApplicationProvider
{
    public const COMMERCE_APPLICATION = 'commerce';

    /** @var FrontendHelper */
    private $frontendHelper;

    public function __construct(TokenStorageInterface $tokenStorage, FrontendHelper $frontendHelper)
    {
        parent::__construct($tokenStorage);
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentApplication(): ?string
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return null !== $this->tokenStorage->getToken()
                ? self::COMMERCE_APPLICATION
                : null;
        }

        return parent::getCurrentApplication();
    }
}
