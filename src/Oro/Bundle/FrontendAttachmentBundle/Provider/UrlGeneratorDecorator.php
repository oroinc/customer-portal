<?php

namespace Oro\Bundle\FrontendAttachmentBundle\Provider;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * Decorator for the URL generator.
 */
class UrlGeneratorDecorator implements UrlGeneratorInterface
{
    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH): string
    {
        if ($name === '_oro_frontend_attachment_filter_image') {
            @trigger_error(
                'The "_oro_frontend_attachment_filter_image" route is deprecated and will be removed in version 4.2, ' .
                'use the "oro_frontend_attachment_filter_image" route instead.',
                E_USER_DEPRECATED
            );
        }

        return $this->urlGenerator->generate($name, $parameters, $referenceType);
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context): void
    {
        $this->urlGenerator->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getContext(): RequestContext
    {
        return $this->urlGenerator->getContext();
    }
}
