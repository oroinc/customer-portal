<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Filter\DictionaryFilter;
use Oro\Bundle\FilterBundle\Filter\EnumFilter;
use Oro\Bundle\FilterBundle\Filter\MultiEnumFilter;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

/**
 * Changes routes for enum and dictionary filters for storefront grids.
 */
class EnumFilterFrontendListener
{
    /** @var FrontendHelper */
    private $frontendHelper;

    public function __construct(FrontendHelper $frontendHelper)
    {
        $this->frontendHelper = $frontendHelper;
    }

    public function onBuildBefore(BuildBefore $event)
    {
        if (!$this->frontendHelper->isFrontendRequest()) {
            return;
        }

        $config = $event->getConfig();

        /** @var array|null $filterColumns */
        $filterColumns = $config->offsetGetByPath(Configuration::COLUMNS_PATH);

        $filtersWithDictionaryFrontendFilter = [
            EnumFilter::FILTER_TYPE_NAME,
            MultiEnumFilter::FILTER_TYPE_NAME,
            DictionaryFilter::FILTER_TYPE_NAME
        ];

        if (!empty($filterColumns)) {
            foreach ($filterColumns as $columnName => $options) {
                if (in_array($options['type'], $filtersWithDictionaryFrontendFilter, true)) {
                    $config->offsetSetByPath(
                        sprintf('%s[%s][dictionaryValueRoute]', Configuration::COLUMNS_PATH, $columnName),
                        'oro_frontend_dictionary_value'
                    );

                    $config->offsetSetByPath(
                        sprintf('%s[%s][dictionarySearchRoute]', Configuration::COLUMNS_PATH, $columnName),
                        'oro_frontend_dictionary_search'
                    );
                }
            }
        }
    }
}
