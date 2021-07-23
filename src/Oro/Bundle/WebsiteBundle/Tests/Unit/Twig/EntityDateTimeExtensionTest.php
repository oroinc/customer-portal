<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Twig;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\LocaleBundle\Twig\DateTimeExtension;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Entity\WebsiteAwareInterface;
use Oro\Bundle\WebsiteBundle\Twig\EntityDateTimeExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;
use Twig\Environment;

class EntityDateTimeExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;

    /** @var \PHPUnit\Framework\MockObject\MockObject|Environment */
    private $env;

    /** @var \PHPUnit\Framework\MockObject\MockObject|DateTimeExtension */
    private $dateTimeExtension;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ConfigManager */
    private $configManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ConfigManager */
    private $globalConfigManager;

    /** @var EntityDateTimeExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->dateTimeExtension = $this->createMock(DateTimeExtension::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->globalConfigManager = $this->createMock(ConfigManager::class);

        $this->env = $this->createMock(Environment::class);
        $this->env->expects(self::any())
            ->method('getExtension')
            ->with(DateTimeExtension::class)
            ->willReturn($this->dateTimeExtension);

        $container = self::getContainerBuilder()
            ->add('oro_config.manager', $this->configManager)
            ->add('oro_config.global', $this->globalConfigManager)
            ->getContainer($this);

        $this->extension = new EntityDateTimeExtension($container);
    }

    public function testFormatByEntityByTimezoneFromWebsite()
    {
        $organization = new Organization();
        $website = new Website();
        $website->setOrganization($organization);
        $entity = $this->createMock(WebsiteAwareInterface::class);
        $entity->expects($this->any())
            ->method('getWebsite')
            ->willReturn($website);
        $timezone = 'Europe/Kyiv';
        $this->configManager->expects($this->exactly(4))
            ->method('get')
            ->with('oro_locale.timezone', false, false, $organization)
            ->willReturn($timezone);

        $date = '01.01.2001';
        $formattedDate = '01/01/2001';
        $this->dateTimeExtension->expects($this->once())
            ->method('formatDateTime')
            ->with($date, ['timeZone' => $timezone])
            ->willReturn($formattedDate);
        $this->dateTimeExtension->expects($this->once())
            ->method('formatDate')
            ->with($date, ['timeZone' => $timezone])
            ->willReturn($formattedDate);
        $this->dateTimeExtension->expects($this->once())
            ->method('formatDay')
            ->with($date, ['timeZone' => $timezone])
            ->willReturn($formattedDate);
        $this->dateTimeExtension->expects($this->once())
            ->method('formatTime')
            ->with($date, ['timeZone' => $timezone])
            ->willReturn($formattedDate);

        self::assertEquals(
            $formattedDate,
            self::callTwigFilter($this->extension, 'oro_format_datetime_by_entity', [$this->env, $date, $entity])
        );
        self::assertEquals(
            $formattedDate,
            self::callTwigFilter($this->extension, 'oro_format_date_by_entity', [$this->env, $date, $entity])
        );
        self::assertEquals(
            $formattedDate,
            self::callTwigFilter($this->extension, 'oro_format_day_by_entity', [$this->env, $date, $entity])
        );
        self::assertEquals(
            $formattedDate,
            self::callTwigFilter($this->extension, 'oro_format_time_by_entity', [$this->env, $date, $entity])
        );
    }

    public function getEntityTimezoneWithOrganization()
    {
        $organization = new Organization();
        $entity = $this->createMock(OrganizationAwareInterface::class);
        $entity->expects($this->any())
            ->method('getOrganization')
            ->willReturn($organization);
        $timezone = 'Europe/Kyiv';
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_locale.timezone', false, false, $organization)
            ->willReturn($timezone);

        $date = '01.01.2001';
        $formattedDate = '01/01/2001';
        $this->dateTimeExtension->expects($this->once())
            ->method('formatDateTime')
            ->with($date, ['timeZone' => $timezone])
            ->willReturn($formattedDate);
        $this->dateTimeExtension->expects($this->once())
            ->method('formatDate')
            ->with($date, ['timeZone' => $timezone])
            ->willReturn($formattedDate);
        $this->dateTimeExtension->expects($this->once())
            ->method('formatDay')
            ->with($date, ['timeZone' => $timezone])
            ->willReturn($formattedDate);
        $this->dateTimeExtension->expects($this->once())
            ->method('formatTime')
            ->with($date, ['timeZone' => $timezone])
            ->willReturn($formattedDate);

        self::assertEquals(
            $formattedDate,
            self::callTwigFilter($this->extension, 'oro_format_datetime_by_entity', [$this->env, $date, $entity])
        );
        self::assertEquals(
            $formattedDate,
            self::callTwigFilter($this->extension, 'oro_format_date_by_entity', [$this->env, $date, $entity])
        );
        self::assertEquals(
            $formattedDate,
            self::callTwigFilter($this->extension, 'oro_format_day_by_entity', [$this->env, $date, $entity])
        );
        self::assertEquals(
            $formattedDate,
            self::callTwigFilter($this->extension, 'oro_format_time_by_entity', [$this->env, $date, $entity])
        );
    }

    public function getEntityTimezone()
    {
        $entity = new \stdClass();
        $timezone = 'Europe/Kyiv';
        $this->globalConfigManager->expects($this->once())
            ->method('get')
            ->with('oro_locale.timezone')
            ->willReturn($timezone);

        $date = '01.01.2001';
        $formattedDate = '01/01/2001';
        $this->dateTimeExtension->expects($this->once())
            ->method('formatDateTime')
            ->with($date, ['timeZone' => $timezone])
            ->willReturn($formattedDate);
        $this->dateTimeExtension->expects($this->once())
            ->method('formatDate')
            ->with($date, ['timeZone' => $timezone])
            ->willReturn($formattedDate);
        $this->dateTimeExtension->expects($this->once())
            ->method('formatDay')
            ->with($date, ['timeZone' => $timezone])
            ->willReturn($formattedDate);
        $this->dateTimeExtension->expects($this->once())
            ->method('formatTime')
            ->with($date, ['timeZone' => $timezone])
            ->willReturn($formattedDate);

        self::assertEquals(
            $formattedDate,
            self::callTwigFilter($this->extension, 'oro_format_datetime_by_entity', [$this->env, $date, $entity])
        );
        self::assertEquals(
            $formattedDate,
            self::callTwigFilter($this->extension, 'oro_format_date_by_entity', [$this->env, $date, $entity])
        );
        self::assertEquals(
            $formattedDate,
            self::callTwigFilter($this->extension, 'oro_format_day_by_entity', [$this->env, $date, $entity])
        );
        self::assertEquals(
            $formattedDate,
            self::callTwigFilter($this->extension, 'oro_format_time_by_entity', [$this->env, $date, $entity])
        );
    }
}
