<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Behat\Context;

use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;

/**
 * Feature for behat test WebsiteBundle
 */
class FeatureContext extends OroFeatureContext
{
    /**
     * @Given /^(?:|I )visit path "(?P<path>.*)" on (?P<subdomain>\w+) subdomain$/
     */
    public function visitPathOnSubdomain(string $path, string $subdomain)
    {
        $currentUrl = $this->getSession()->getCurrentUrl();
        $parseUrl = parse_url($currentUrl);
        $scheme = $parseUrl['scheme'];
        $host = $parseUrl['host'];
        $port = isset($parseUrl['port']) ? ':' . $parseUrl['port'] : '';

        $host = $subdomain . '.' . $host;
        $uri = sprintf('%s://%s%s%s', $scheme, $host, $port, $path);

        $this->visitPath($uri);
    }

    /**
     * @Given /^(?:|I )check path "(?P<path>.*)" is located on the base domain$/
     */
    public function checkPathIsLocateOnTheBaseDomain(string $path)
    {
        $currentUrl = $this->getSession()->getCurrentUrl();
        $baseUrl = rtrim($this->getMinkParameter('base_url'), '/');

        static::assertStringContainsString($path, $currentUrl, 'Url does not contain path');
        static::assertStringContainsString($baseUrl, $currentUrl, 'Url does not contain base domain');
    }
}
