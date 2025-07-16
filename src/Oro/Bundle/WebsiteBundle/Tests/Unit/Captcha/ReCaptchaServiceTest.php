<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Captcha;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FormBundle\Captcha\ReCaptchaClientFactory;
use Oro\Bundle\FormBundle\DependencyInjection\Configuration;
use Oro\Bundle\FormBundle\Form\Type\ReCaptchaType;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Oro\Bundle\WebsiteBundle\Captcha\ReCaptchaService;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReCaptcha\ReCaptcha;
use ReCaptcha\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ReCaptchaServiceTest extends TestCase
{
    private ReCaptchaClientFactory&MockObject $reCaptchaClientFactory;
    private ConfigManager&MockObject $configManager;
    private RequestStack&MockObject $requestStack;
    private SymmetricCrypterInterface&MockObject $crypter;
    private WebsiteUrlResolver&MockObject $urlResolver;
    private WebsiteManager&MockObject $websiteManager;
    private FrontendHelper&MockObject $frontendHelper;
    private ReCaptchaService $captchaService;

    #[\Override]
    protected function setUp(): void
    {
        $this->reCaptchaClientFactory = $this->createMock(ReCaptchaClientFactory::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->crypter = $this->createMock(SymmetricCrypterInterface::class);

        $this->urlResolver = $this->createMock(WebsiteUrlResolver::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->captchaService = new ReCaptchaService(
            $this->reCaptchaClientFactory,
            $this->configManager,
            $this->requestStack,
            $this->crypter
        );
        $this->captchaService->setUrlResolver($this->urlResolver);
        $this->captchaService->setWebsiteManager($this->websiteManager);
        $this->captchaService->setFrontendHelper($this->frontendHelper);
    }

    public function testIsConfiguredReturnsTrueWhenKeysArePresent(): void
    {
        $this->configManager->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [Configuration::getConfigKey(Configuration::RECAPTCHA_PUBLIC_KEY), false, false, null, 'publicKey'],
                [
                    Configuration::getConfigKey(Configuration::RECAPTCHA_PRIVATE_KEY),
                    false,
                    false,
                    null,
                    'encryptedPrivateKey'
                ],
                [Configuration::getConfigKey(Configuration::RECAPTCHA_MINIMAL_ALLOWED_SCORE), false, false, null, 0.5]
            ]);

        $this->crypter->expects($this->once())
            ->method('decryptData')
            ->with('encryptedPrivateKey')
            ->willReturn('privateKey');

        $this->assertTrue($this->captchaService->isConfigured());
    }

    public function testIsConfiguredReturnsFalseWhenKeysAreAbsent(): void
    {
        $this->configManager->expects($this->any())
            ->method('get')
            ->willReturn(null);

        $this->assertFalse($this->captchaService->isConfigured());
    }

    public function testIsVerifiedInvalid(): void
    {
        $this->reCaptchaClientFactory->expects(self::never())
            ->method('create');

        self::assertFalse($this->captchaService->isVerified(null));
    }

    /**
     * @dataProvider verificationDataProvider
     */
    public function testIsVerified(bool $isSuccess): void
    {
        $secret = 'captchaResponseValue';

        $threshold = 0.5;
        $this->configManager->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [Configuration::getConfigKey(Configuration::RECAPTCHA_PUBLIC_KEY), false, false, null, 'publicKey'],
                [
                    Configuration::getConfigKey(Configuration::RECAPTCHA_PRIVATE_KEY),
                    false,
                    false,
                    null,
                    'encryptedPrivateKey'
                ],
                [
                    Configuration::getConfigKey(Configuration::RECAPTCHA_MINIMAL_ALLOWED_SCORE),
                    false,
                    false,
                    null,
                    $threshold
                ],
                ['oro_ui.application_url', false, false, null, 'http://mysite.com'],
            ]);

        $website = new Website();
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);
        $this->urlResolver->expects($this->once())
            ->method('getWebsiteUrl')
            ->with($website)
            ->willReturn('http://mysite.com/us/');

        $this->crypter->expects($this->once())
            ->method('decryptData')
            ->with('encryptedPrivateKey')
            ->willReturn('privateKey');

        $response = $this->createMock(Response::class);
        $response->expects($this->any())
            ->method('isSuccess')
            ->willReturn($isSuccess);

        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getClientIp')
            ->willReturn('127.0.0.1');
        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $reCaptchaMock = $this->createMock(ReCaptcha::class);
        $reCaptchaMock->expects($this->once())
            ->method('setExpectedHostname')
            ->with('mysite.com')
            ->willReturnSelf();
        $reCaptchaMock->expects($this->once())
            ->method('setScoreThreshold')
            ->with($threshold)
            ->willReturnSelf();
        $reCaptchaMock->expects($this->once())
            ->method('verify')
            ->with($secret, '127.0.0.1')
            ->willReturn($response);

        $this->reCaptchaClientFactory->expects($this->once())
            ->method('create')
            ->with('privateKey')
            ->willReturn($reCaptchaMock);

        $this->assertEquals($isSuccess, $this->captchaService->isVerified($secret));
    }

    /**
     * @dataProvider verificationDataProvider
     */
    public function testIsVerifiedNonFrontend(bool $isSuccess): void
    {
        $secret = 'captchaResponseValue';

        $threshold = 0.5;
        $this->configManager->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [Configuration::getConfigKey(Configuration::RECAPTCHA_PUBLIC_KEY), false, false, null, 'publicKey'],
                [
                    Configuration::getConfigKey(Configuration::RECAPTCHA_PRIVATE_KEY),
                    false,
                    false,
                    null,
                    'encryptedPrivateKey'
                ],
                [
                    Configuration::getConfigKey(Configuration::RECAPTCHA_MINIMAL_ALLOWED_SCORE),
                    false,
                    false,
                    null,
                    $threshold
                ],
                ['oro_ui.application_url', false, false, null, 'http://mysite.com'],
            ]);

        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->websiteManager->expects($this->never())
            ->method('getCurrentWebsite');
        $this->urlResolver->expects($this->never())
            ->method('getWebsiteUrl');

        $this->crypter->expects($this->once())
            ->method('decryptData')
            ->with('encryptedPrivateKey')
            ->willReturn('privateKey');

        $response = $this->createMock(Response::class);
        $response->expects($this->any())
            ->method('isSuccess')
            ->willReturn($isSuccess);

        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getClientIp')
            ->willReturn('127.0.0.1');
        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $reCaptchaMock = $this->createMock(ReCaptcha::class);
        $reCaptchaMock->expects($this->once())
            ->method('setExpectedHostname')
            ->with('mysite.com')
            ->willReturnSelf();
        $reCaptchaMock->expects($this->once())
            ->method('setScoreThreshold')
            ->with($threshold)
            ->willReturnSelf();
        $reCaptchaMock->expects($this->once())
            ->method('verify')
            ->with($secret, '127.0.0.1')
            ->willReturn($response);

        $this->reCaptchaClientFactory->expects($this->once())
            ->method('create')
            ->with('privateKey')
            ->willReturn($reCaptchaMock);

        $this->assertEquals($isSuccess, $this->captchaService->isVerified($secret));
    }

    public function verificationDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    public function testGetFormTypeReturnsReCaptchaType(): void
    {
        $this->assertEquals(ReCaptchaType::class, $this->captchaService->getFormType());
    }
}
