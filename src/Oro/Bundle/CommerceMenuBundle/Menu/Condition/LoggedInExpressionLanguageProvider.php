<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\Condition;

use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class LoggedInExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    /** @var TokenAccessorInterface */
    private $tokenAccessor;

    public function __construct(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
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
