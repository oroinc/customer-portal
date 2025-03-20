<?php

namespace Oro\Bundle\FrontendBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * The base class for fixtures that load frontend theme
 */
abstract class AbstractLoadFrontendTheme extends AbstractFixture
{
    abstract protected function getFrontendTheme(): ?string;
}
