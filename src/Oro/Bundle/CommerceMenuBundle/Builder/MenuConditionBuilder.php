<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Knp\Menu\ItemInterface;

use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class MenuConditionBuilder implements BuilderInterface
{
    const IS_ALLOWED_OPTION_KEY     = 'isAllowed';
    const CONDITION_KEY             = 'condition';
    const DEFAULT_IS_ALLOWED_POLICY = true;

    /** @var ExpressionLanguage */
    private $expressionLanguage;

    /**
     * @param ExpressionLanguage $expressionLanguage
     */
    public function __construct(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     * {@inheritdoc}
     */
    public function build(ItemInterface $menu, array $options = [], $alias = null)
    {
        $this->applyConditionsRecursively($menu);
    }

    /**
     * @param ItemInterface $menu
     */
    private function applyConditionsRecursively(ItemInterface $menu)
    {
        $menuChildren = $menu->getChildren();

        foreach ($menuChildren as $menuChild) {
            $this->applyConditionsRecursively($menuChild);
        }

        if ($menu->getExtra(self::CONDITION_KEY) && $menu->getExtra(self::IS_ALLOWED_OPTION_KEY) !== false) {
            $result = (bool)$this->expressionLanguage->evaluate($menu->getExtra(self::CONDITION_KEY));
            $menu->setExtra(self::IS_ALLOWED_OPTION_KEY, $result);
        }
    }
}
