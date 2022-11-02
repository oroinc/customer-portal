<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Form\Handler\ConfigHandler;
use Oro\Bundle\ConfigBundle\Provider\AbstractProvider;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Provider\CustomerConfigurationFormProvider;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SyncBundle\Content\DataUpdateTopicSender;
use Oro\Bundle\SyncBundle\Content\TagGeneratorInterface;
use Psr\Container\ContainerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The controller to handle the the customer configuration.
 */
class CustomerConfigurationController implements ServiceSubscriberInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @Route(
     *      "/customer/{id}/{activeGroup}/{activeSubGroup}",
     *      name="oro_customer_config",
     *      requirements={"id"="\d+"},
     *      defaults={"activeGroup" = null, "activeSubGroup" = null}
     * )
     * @Template()
     * @AclAncestor("oro_customer_update")
     */
    public function customerConfigAction(
        Request $request,
        Customer $entity,
        ?string $activeGroup = null,
        ?string $activeSubGroup = null
    ): array {
        $provider = $this->getConfigFormProvider();
        $manager = $this->getConfigManager();
        $prevScopeId = $manager->getScopeId();
        $manager->setScopeId($entity->getId());
        [$activeGroup, $activeSubGroup] = $provider->chooseActiveGroups($activeGroup, $activeSubGroup);
        $form = null;
        if (null !== $activeSubGroup) {
            $form = $provider->getForm($activeSubGroup, $manager);
            if ($this->getConfigHandler()->setConfigManager($manager)->process($form, $request)) {
                $request->getSession()->getFlashBag()->add(
                    'success',
                    $this->getTranslator()->trans('oro.config.controller.config.saved.message')
                );

                // outdate content tags, it's only special case for generation that are not covered by NavigationBundle
                $this->getDataUpdateTopicSender()->send($this->getTagGenerator()->generate([
                    'name'   => 'customer_configuration',
                    'params' => [$activeGroup, $activeSubGroup]
                ]));

                // recreate form to drop values for fields with use_parent_scope_value
                $form = $provider->getForm($activeSubGroup, $manager);
                $form->setData($manager->getSettingsByForm($form));
            }
        }
        $manager->setScopeId($prevScopeId);

        return [
            'entity'           => $entity,
            'data'             => $provider->getJsTree(),
            'form'             => $form?->createView(),
            'activeGroup'      => $activeGroup,
            'activeSubGroup'   => $activeSubGroup,
            'scopeEntity'      => $entity,
            'scopeEntityClass' => Customer::class,
            'scopeEntityId'    => $entity->getId(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            CustomerConfigurationFormProvider::class,
            ConfigManager::class,
            ConfigHandler::class,
            TranslatorInterface::class,
            TagGeneratorInterface::class,
            DataUpdateTopicSender::class,
        ];
    }

    private function getConfigFormProvider(): AbstractProvider
    {
        return $this->container->get(CustomerConfigurationFormProvider::class);
    }

    private function getConfigManager(): ConfigManager
    {
        return $this->container->get(ConfigManager::class);
    }

    private function getConfigHandler(): ConfigHandler
    {
        return $this->container->get(ConfigHandler::class);
    }

    private function getTranslator(): TranslatorInterface
    {
        return $this->container->get(TranslatorInterface::class);
    }

    private function getTagGenerator(): TagGeneratorInterface
    {
        return $this->container->get(TagGeneratorInterface::class);
    }

    private function getDataUpdateTopicSender(): DataUpdateTopicSender
    {
        return $this->container->get(DataUpdateTopicSender::class);
    }
}
