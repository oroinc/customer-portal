<?php

namespace Oro\Bundle\WebsiteBundle\Twig;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration;
use Oro\Bundle\LocaleBundle\Twig\DateTimeExtension;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\WebsiteBundle\Entity\WebsiteAwareInterface;
use Oro\Bundle\WebsiteBundle\Helper\WebsiteAwareEntityHelper;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Provides a Twig filters to format date and time for specific entity timezone:
 *   - oro_format_datetime_by_entity
 *   - oro_format_date_by_entity
 *   - oro_format_day_by_entity
 *   - oro_format_time_by_entity
 */
class EntityDateTimeExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    #[\Override]
    public function getFilters()
    {
        return [
            new TwigFilter(
                'oro_format_datetime_by_entity',
                [$this, 'formatDateTimeByEntity'],
                ['needs_environment' => true]
            ),
            new TwigFilter(
                'oro_format_date_by_entity',
                [$this, 'formatDateByEntity'],
                ['needs_environment' => true]
            ),
            new TwigFilter(
                'oro_format_day_by_entity',
                [$this, 'formatDayByEntity'],
                ['needs_environment' => true]
            ),
            new TwigFilter(
                'oro_format_time_by_entity',
                [$this, 'formatTimeByEntity'],
                ['needs_environment' => true]
            ),
        ];
    }

    /**
     * @param Environment $env
     * @param \DateTime|string|int $date
     * @param object $entity
     * @param array $options
     * @return string
     */
    public function formatDateTimeByEntity(Environment $env, $date, $entity, array $options = []): string
    {
        $options['timeZone'] = $this->getEntityTimezone($entity);

        return $this->getDateTimeExtension($env)->formatDateTime($date, $options);
    }

    /**
     * @param Environment $env
     * @param \DateTime|string|int $date
     * @param object $entity
     * @param array $options
     * @return string
     */
    public function formatDateByEntity(Environment $env, $date, $entity, array $options = []): string
    {
        $options['timeZone'] = $this->getEntityTimezone($entity);

        return $this->getDateTimeExtension($env)->formatDate($date, $options);
    }

    /**
     * @param Environment $env
     * @param \DateTime|string|int $date
     * @param object $entity
     * @param array $options
     * @return string
     */
    public function formatDayByEntity(Environment $env, $date, $entity, array $options = []): string
    {
        $options['timeZone'] = $this->getEntityTimezone($entity);

        return $this->getDateTimeExtension($env)->formatDay($date, $options);
    }

    /**
     * @param Environment $env
     * @param \DateTime|string|int $date
     * @param object $entity
     * @param array $options
     * @return string
     */
    public function formatTimeByEntity(Environment $env, $date, $entity, array $options = []): string
    {
        $options['timeZone'] = $this->getEntityTimezone($entity);

        return $this->getDateTimeExtension($env)->formatTime($date, $options);
    }

    /**
     * @param object $entity
     * @return mixed
     */
    private function getEntityTimezone($entity)
    {
        $organization = null;
        if (
            ($entity instanceof WebsiteAwareInterface || $this->getWebsiteAwareEntityHelper()->isWebsiteAware($entity))
            && $entity->getWebsite()
        ) {
            $organization = $entity->getWebsite()->getOrganization();
        } elseif ($entity instanceof OrganizationAwareInterface && $entity->getOrganization()) {
            $organization = $entity->getOrganization();
        }

        if ($organization) {
            return $this->getConfigManager()->get(
                Configuration::getConfigKeyByName('timezone'),
                false,
                false,
                $organization
            );
        }

        return $this->getSystemConfigManager()->get(Configuration::getConfigKeyByName('timezone'));
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            'oro_config.global' => ConfigManager::class,
            WebsiteAwareEntityHelper::class,
            ConfigManager::class
        ];
    }

    private function getSystemConfigManager(): ConfigManager
    {
        return $this->container->get('oro_config.global');
    }

    private function getWebsiteAwareEntityHelper(): WebsiteAwareEntityHelper
    {
        return $this->container->get(WebsiteAwareEntityHelper::class);
    }

    private function getConfigManager(): ConfigManager
    {
        return $this->container->get(ConfigManager::class);
    }

    private function getDateTimeExtension(Environment $env): DateTimeExtension
    {
        return $env->getExtension(DateTimeExtension::class);
    }
}
