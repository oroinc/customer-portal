<?php

namespace Oro\Bundle\FrontendBundle\Controller\Frontend;

use Oro\Bundle\FrontendLocalizationBundle\Controller\Frontend\RedirectLocalizationControllerTrait;
use Oro\Bundle\FrontendLocalizationBundle\Helper\LocalizedSlugRedirectHelper;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManager;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Manager\LocalizationManager;
use Oro\Bundle\PricingBundle\Manager\UserCurrencyManager;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * AJAX controller for changing current localization and currency.
 */
class AjaxCurrencyAndLocalizationController extends AbstractController
{
    use RedirectLocalizationControllerTrait;

    #[Route(
        path: '/set-current-currency-and-localization',
        name: 'oro_frontend_set_current_currency_and_localization',
        methods: ['POST']
    )]
    #[CsrfProtection]
    public function setCurrentCurrencyAndLocalizationAction(Request $request): JsonResponse
    {
        $currencyResponse = $this->doSetCurrentCurrency($request);
        $localizationResponse = $this->doSetCurrentLocalizationAction($request);

        return new JsonResponse(array_merge($currencyResponse, $localizationResponse));
    }

    private function doSetCurrentCurrency(Request $request): array
    {
        $currency = $request->get('currency');
        $response['currencySuccessful'] = false;
        $userCurrencyManager = $this->container->get(UserCurrencyManager::class);
        if (\in_array($currency, $userCurrencyManager->getAvailableCurrencies(), true)) {
            $userCurrencyManager->saveSelectedCurrency($currency);
            $response['currencySuccessful'] = true;
        }

        return $response;
    }

    private function doSetCurrentLocalizationAction(Request $request): array
    {
        $localization = $this->getLocalization($request);
        $localizationManager = $this->container->get(UserLocalizationManager::class);
        if (
            $localization instanceof Localization
            && \array_key_exists($localization->getId(), $localizationManager->getEnabledLocalizations())
        ) {
            $localizationManager->setCurrentLocalization($localization);

            $fromUrl = $this->generateUrlWithContext($request);
            if ($fromUrl) {
                $redirectHelper = $this->container->get(LocalizedSlugRedirectHelper::class);
                if ($request->server->has('WEBSITE_PATH')) {
                    $toUrl = $this->getUrlForWebsitePath($request, $fromUrl, $localization, $redirectHelper);
                } else {
                    $toUrl = $redirectHelper->getLocalizedUrl($fromUrl, $localization);
                    $toUrl = $this->rebuildQueryString($toUrl, $request);
                }
            } else {
                return ['localizationSuccessful' => true, 'redirectTo' => $this->generateUrlByReferer($request)];
            }

            return ['localizationSuccessful' => true, 'redirectTo' => $toUrl];
        }

        return ['localizationSuccessful' => false];
    }

    private function getLocalization(Request $request): ?Localization
    {
        $id = $request->get('localization');

        return isset($id) ? $this->container->get(LocalizationManager::class)->getLocalization($id, false) : null;
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                UserCurrencyManager::class,
                LocalizationManager::class,
                UserLocalizationManager::class,
                LocalizedSlugRedirectHelper::class,
            ]
        );
    }
}
