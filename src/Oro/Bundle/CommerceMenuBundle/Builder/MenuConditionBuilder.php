<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Knp\Menu\ItemInterface;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;

class MenuConditionBuilder implements BuilderInterface
{
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

        if ($menu->getExtra(self::CONDITION_KEY) && $menu->isDisplayed() !== false) {
            $result = (bool)$this->expressionLanguage->evaluate($menu->getExtra(self::CONDITION_KEY));
            $menu->setDisplay($result);
        }
    }
}
