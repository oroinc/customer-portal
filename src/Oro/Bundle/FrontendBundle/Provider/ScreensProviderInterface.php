<?php

namespace Oro\Bundle\FrontendBundle\Provider;

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
