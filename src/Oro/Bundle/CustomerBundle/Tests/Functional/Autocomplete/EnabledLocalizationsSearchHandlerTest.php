<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Autocomplete;

use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\CustomerBundle\Autocomplete\EnabledLocalizationsSearchHandler;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration;
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

    /** @var EnabledLocalizationsSearchHandler */
    private $searchHandler;

    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $this->loadFixtures([LoadLocalizationData::class]);
        $this->searchHandler = self::getContainer()
            ->get('oro_customer.autocomplete.enabled_localizations.search_handler');
        $this->prepareGlobalLocalizations([$this->getReference('en_US')->getId()]);
        self::getContainer()->get('oro_search.search.engine.indexer')->reindex(Localization::class);
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
        $this->prepareGlobalLocalizations($expectedLocalizations);

        $result = $this->searchHandler->search(';', 1, 10, false);
        $this->assertSearchResult($result, $expectedLocalizations);
    }

    public function testSearchById(): void
    {
        $expectedLocalizations = [$this->getReference('en_CA')->getId()];
        $this->prepareGlobalLocalizations($expectedLocalizations);

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
        $this->assertEquals(sort($expected), sort($result));
    }

    private function prepareGlobalLocalizations(array $localizationIds): void
    {
        $key = Configuration::getConfigKeyByName(Configuration::ENABLED_LOCALIZATIONS);
        self::getConfigManager()->set($key, $localizationIds);
        self::getConfigManager()->flush();
    }
}
