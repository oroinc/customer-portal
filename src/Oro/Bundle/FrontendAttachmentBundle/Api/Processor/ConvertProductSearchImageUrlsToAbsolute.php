<?php

namespace Oro\Bundle\FrontendAttachmentBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;
use Oro\Bundle\ApiBundle\Provider\ApiUrlResolver;
use Oro\Bundle\UIBundle\Tools\UrlHelper;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Converts relative image URLs to absolute for ProductSearch entity when API requires absolute URLs.
 *
 * This processor must run before the ComputeProductSearchImages processor
 * to ensure that URL conversion happens on raw index data before it's transformed into the images array.
 */
class ConvertProductSearchImageUrlsToAbsolute implements ProcessorInterface
{
    private const IMAGE_FIELDS = [
        'text_image_product_large',
        'text_image_product_medium',
    ];

    public function __construct(
        private readonly UrlHelper $urlHelper,
        private readonly ApiUrlResolver $apiUrlResolver
    ) {
    }

    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var CustomizeLoadedDataContext $context */

        if (!$this->apiUrlResolver->shouldUseAbsoluteUrls()) {
            return;
        }

        $data = $context->getData();
        if (!\is_array($data)) {
            return;
        }

        $hasChanges = false;

        foreach (self::IMAGE_FIELDS as $imageField) {
            if (!isset($data[$imageField]) || !is_string($data[$imageField])) {
                continue;
            }

            $absoluteUrl = $this->urlHelper->getAbsoluteUrl($data[$imageField]);
            if ($absoluteUrl !== $data[$imageField]) {
                $data[$imageField] = $absoluteUrl;
                $hasChanges = true;
            }
        }

        if ($hasChanges) {
            $context->setData($data);
        }
    }
}
