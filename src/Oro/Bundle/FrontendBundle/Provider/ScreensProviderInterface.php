<?php

namespace Oro\Bundle\FrontendBundle\Provider;

/**
 * Defines the contract for providing screen configurations in the storefront.
 *
 * Implementations of this interface are responsible for managing and providing access to
 * screen definitions that control the layout and display of different sections of the
 * storefront user interface.
 */
interface ScreensProviderInterface
{
    /**
     * @return array
     */
    public function getScreens();

    /**
     * @param string $screenName
     *
     * @return array|null
     */
    public function getScreen($screenName);

    /**
     * @param string $screenName
     *
     * @return bool
     */
    public function hasScreen($screenName);
}
