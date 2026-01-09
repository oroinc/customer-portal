<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\Condition;

use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * Provides the is_logged_in() function for menu condition expression language.
 *
 * This expression language provider registers a function that allows menu conditions to check
 * whether the current user is authenticated, enabling dynamic menu visibility based on login status.
 */
class LoggedInExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    /** @var TokenAccessorInterface */
    private $tokenAccessor;

    public function __construct(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('is_logged_in', function () {
                return 'is_logged_in()';
            }, [$this, 'isLoggedIn'])
        ];
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->tokenAccessor->hasUser();
    }
}
