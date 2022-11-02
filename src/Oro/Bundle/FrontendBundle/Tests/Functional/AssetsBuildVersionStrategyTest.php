<?php
namespace Oro\Bundle\FrontendBundle\Tests\Functional;

use Oro\Bundle\AssetBundle\Tests\Functional\VersionStrategy\BuildVersionStrategyTest;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class AssetsBuildVersionStrategyTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
    }

    public function testFrontendHeadLinksVersioning()
    {
        $crawler = $this->requestOroFrontendRootRoute();

        $headLinks = $crawler->filterXPath('//head/link[starts-with(@href, "/")]');
        foreach ($headLinks as $headLink) {
            $headLinkHref = $headLink->getAttribute('href');

            self::assertMatchesRegularExpression(
                BuildVersionStrategyTest::VERSION_REGEXP,
                $headLinkHref,
                sprintf(
                    "Frontend head link's 'href' is not versioned properly. HRef value: %s",
                    $headLinkHref
                )
            );
        }
    }

    public function testFrontendBodyScriptsVersioning()
    {
        $crawler = $this->requestOroFrontendRootRoute();

        $headScripts = $crawler->filterXPath('//body//script[starts-with(@src, "/")]');
        foreach ($headScripts as $headScript) {
            $headScriptSource = $headScript->getAttribute('src');

            self::assertMatchesRegularExpression(
                BuildVersionStrategyTest::VERSION_REGEXP,
                $headScriptSource,
                sprintf(
                    "Frontend body script's source is not versioned properly. Source value: %s",
                    $headScriptSource
                )
            );
        }
    }

    public function testFrontendRoutesJsonVersioning()
    {
        $this->requestOroFrontendRootRoute();
        $result = $this->client->getResponse();

        self::assertMatchesRegularExpression(
            '/\/frontend_routes\.json' . BuildVersionStrategyTest::VERSION_REGEXP_BASE . '(\&|\s+|\")/',
            $result->getContent()
        );
    }

    public function testFrontendTranslationsJsonVersioning()
    {
        $this->requestOroFrontendRootRoute();
        $result = $this->client->getResponse();

        self::assertMatchesRegularExpression(
            '/\/en\.json' . BuildVersionStrategyTest::VERSION_REGEXP_BASE . '(\&|\s+|\")/',
            $result->getContent()
        );
    }

    private function requestOroFrontendRootRoute(): ?Crawler
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_frontend_root'));

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        return $crawler;
    }
}
