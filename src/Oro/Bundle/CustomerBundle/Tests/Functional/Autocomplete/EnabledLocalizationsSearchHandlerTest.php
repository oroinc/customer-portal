<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Autocomplete;

use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\CustomerBundle\Autocomplete\EnabledLocalizationsSearchHandler;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Tests\Functional\DataFixtures\LoadLocalizationData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 * @group CommunityEdition
 */
class EnabledLocalizationsSearchHandlerTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;

    private ?array $initialEnabledLocalizations;
    private EnabledLocalizationsSearchHandler $searchHandler;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $this->loadFixtures([LoadLocalizationData::class]);

        $configManager = self::getConfigManager();
        $this->initialEnabledLocalizations = $configManager->get('oro_locale.enabled_localizations');
        $configManager->set('oro_locale.enabled_localizations', [$this->getReference('en_US')->getId()]);
        $configManager->flush();

        $this->searchHandler = self::getContainer()
            ->get('oro_customer.autocomplete.enabled_localizations.search_handler');
        self::getContainer()->get('oro_search.search.engine.indexer')->reindex(Localization::class);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $configManager = self::getConfigManager();
        $configManager->set('oro_locale.enabled_localizations', $this->initialEnabledLocalizations);
        $configManager->flush();
    }

    public function testSearchWithInvalidSearch(): void
    {
        $result = $this->searchHandler->search('', 1, 10, false);
        $this->assertSearchResult($result, []);
    }

    public function testSearch(): void
    {
        $result = $this->searchHandler->search(';', 1, 10, false);
        $this->assertSearchResult($result, [$this->getReference('en_US')->getId()]);

        $expectedLocalizations = [$this->getReference('en_CA')->getId()];
        $configManager = self::getConfigManager();
        $configManager->set('oro_locale.enabled_localizations', $expectedLocalizations);
        $configManager->flush();

        $result = $this->searchHandler->search(';', 1, 10, false);
        $this->assertSearchResult($result, $expectedLocalizations);
    }

    public function testSearchById(): void
    {
        $expectedLocalizations = [$this->getReference('en_CA')->getId()];
        $configManager = self::getConfigManager();
        $configManager->set('oro_locale.enabled_localizations', $expectedLocalizations);
        $configManager->flush();

        $this->assertSearchResult(
            $this->searchHandler->search(sprintf('%s;', $this->getReference('en_CA')->getId()), 1, 10, true),
            $expectedLocalizations
        );
    }

    public function testSearchByIdNotExistingId(): void
    {
        $this->assertSearchResult($this->searchHandler->search('2;', 1, 10, true), []);
    }

    /**
     * @param array|Localization[] $result
     * @param array $expected
     */
    private function assertSearchResult(array $result, array $expected): void
    {
        $searchItems = $result['results'];
        $this->assertCount(count($expected), $searchItems);

        $result = array_map(fn (array $searchResult) => $searchResult['id'], $searchItems);
        sort($expected);
        sort($result);
        $this->assertEquals($expected, $result);
    }
}
