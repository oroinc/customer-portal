<?php

declare(strict_types=1);

namespace Oro\Bundle\CommerceMenuBundle\Form\Extension;

use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Form\Type\MenuUpdateType;
use Oro\Bundle\NavigationBundle\Menu\ConfigurationBuilder;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Adds an optional warning to the {@see MenuUpdateType}.
 */
class MenuUpdateWarningExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [MenuUpdateType::class];
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var ItemInterface $menu */
        $menu = $form->getConfig()->getOption('menu');
        $warning = $menu->getExtra(ConfigurationBuilder::WARNING);
        if ($warning) {
            $view->vars['warning'] = $warning;
        }
    }
}
