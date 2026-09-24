<?php

namespace Oro\Bundle\CommerceMenuBundle\Provider;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provides a text representation of a user agent condition of a storefront menu item, the way the menu item
 * form words it: the group the condition belongs to, what it does to the user agent and the value it does
 * it with.
 */
class MenuUserAgentConditionEntityNameProvider implements EntityNameProviderInterface
{
    private const NAME_KEY = 'oro.commercemenu.menu_user_agent_condition.name';
    private const OPERATION_KEY = 'oro.commercemenu.menu_user_agent_condition.operation.%s.label';

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    #[\Override]
    public function getName($format, $locale, $entity)
    {
        if (!$entity instanceof MenuUserAgentCondition) {
            return false;
        }

        return $this->trans(self::NAME_KEY, $locale, [
            '%group%' => (int)$entity->getConditionGroupIdentifier() + 1,
            '%operation%' => $this->trans(
                sprintf(self::OPERATION_KEY, (string)$entity->getOperation()),
                $locale
            ),
            '%value%' => (string)$entity->getValue(),
        ]);
    }

    #[\Override]
    public function getNameDQL($format, $locale, $className, $alias)
    {
        return false;
    }

    private function trans(string $key, string|Localization|null $locale, array $parameters = []): string
    {
        if ($locale instanceof Localization) {
            $locale = $locale->getLanguageCode();
        }

        return $this->translator->trans($key, $parameters, null, $locale);
    }
}
