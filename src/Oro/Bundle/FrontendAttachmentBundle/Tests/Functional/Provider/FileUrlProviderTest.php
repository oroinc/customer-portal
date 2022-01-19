<?php

namespace Oro\Bundle\FrontendAttachmentBundle\Tests\Functional\Provider;

use Oro\Bundle\ActionBundle\Provider\CurrentApplicationProviderInterface;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\DigitalAssetBundle\Entity\DigitalAsset;
use Oro\Bundle\DigitalAssetBundle\Tests\Functional\DataFixtures\LoadDigitalAssetData;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager as EntityConfigManager;
use Oro\Bundle\FrontendAttachmentBundle\Provider\FileUrlProvider;
use Oro\Bundle\FrontendBundle\Provider\FrontendCurrentApplicationProvider;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * Frontend application url access must follow:
 * Acl Protected  Applications            Is Access granted
 * YES            backoffice, frontstore  true (no ACL checks performed for FrontStore user)
 * YES            backoffice              false
 * YES            frontstore              true
 * NO             backoffice, frontstore  true
 * NO             backoffice              true
 * NO             frontstore              true
 */
class FileUrlProviderTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;

    private const ORO_FRONTEND_GUEST_ACCESS_ENABLED = 'oro_frontend.guest_access_enabled';

    private FileUrlProvider $fileUrlProvider;

    protected function setUp(): void
    {
        $this->initClient(
            [],
            self::generateBasicAuthHeader(LoadCustomerUserData::AUTH_USER, LoadCustomerUserData::AUTH_PW)
        );

        $this->loadFixtures([LoadDigitalAssetData::class]);

        $this->fileUrlProvider = self::getContainer()->get('oro_frontend.provider.file_url');
    }

    private function getEntityConfigManager(): EntityConfigManager
    {
        return self::getContainer()->get('oro_entity_config.config_manager');
    }

    /**
     * @dataProvider frontendOrPublicDataProvider
     */
    public function testGetResizedImageUrlFrontendApp(array $fileApp, bool $isCoveredByAcl, bool $admin): void
    {
        $this->updateEntityAttachmentConfig($fileApp, $isCoveredByAcl);

        /** @var DigitalAsset $digitalAsset1 */
        $digitalAsset1 = $this->getReference(LoadDigitalAssetData::DIGITAL_ASSET_1);
        $url = $this->fileUrlProvider->getResizedImageUrl($digitalAsset1->getSourceFile(), '400', '400');

        if ($admin) {
            $this->assertStringContainsString('admin', $url);
        } else {
            $this->assertStringNotContainsString('admin', $url);
        }
    }

    /**
     * @dataProvider frontendOrPublicDataProvider
     */
    public function testGetFilteredImageUrlFrontendApp(array $fileApp, bool $isCoveredByAcl, bool $admin): void
    {
        $this->updateEntityAttachmentConfig($fileApp, $isCoveredByAcl);

        /** @var DigitalAsset $digitalAsset1 */
        $digitalAsset1 = $this->getReference(LoadDigitalAssetData::DIGITAL_ASSET_1);
        $file = $digitalAsset1->getSourceFile();
        $url = $this->fileUrlProvider->getFilteredImageUrl($file, 'original');

        if ($admin) {
            $this->assertStringContainsString('admin', $url);
        } else {
            $this->assertStringNotContainsString('admin', $url);
        }
    }

    public function frontendOrPublicDataProvider(): array
    {
        return [
            [
                'fileApplications' => [
                    CurrentApplicationProviderInterface::DEFAULT_APPLICATION,
                    FrontendCurrentApplicationProvider::COMMERCE_APPLICATION,
                ],
                'isCoveredByAcl' => true,
                'admin' => false
            ],
            [
                'fileApplications' => [CurrentApplicationProviderInterface::DEFAULT_APPLICATION],
                'isCoveredByAcl' => true,
                'admin' => true
            ],
            [
                'fileApplications' => [FrontendCurrentApplicationProvider::COMMERCE_APPLICATION],
                'isCoveredByAcl' => true,
                'admin' => false
            ],
            [
                'fileApplications' => [
                    CurrentApplicationProviderInterface::DEFAULT_APPLICATION,
                    FrontendCurrentApplicationProvider::COMMERCE_APPLICATION,
                ],
                'isCoveredByAcl' => false,
                'admin' => false
            ],
            [
                'fileApplications' => [CurrentApplicationProviderInterface::DEFAULT_APPLICATION],
                'isCoveredByAcl' => false,
                'admin' => false
            ],
            [
                'fileApplications' => [FrontendCurrentApplicationProvider::COMMERCE_APPLICATION],
                'isCoveredByAcl' => false,
                'admin' => false
            ]
        ];
    }

    /**
     * @param array $fileApplications
     * @param bool $isCoveredByAcl
     */
    private function updateEntityAttachmentConfig(array $fileApplications, bool $isCoveredByAcl): void
    {
        $configManager = $this->getEntityConfigManager();
        $fieldConfig = $configManager
            ->getProvider('attachment')
            ->getConfig(DigitalAsset::class, 'sourceFile');

        $fieldConfig->set('file_applications', $fileApplications);
        $fieldConfig->set('acl_protected', $isCoveredByAcl);

        $configManager->flush();
    }
}
