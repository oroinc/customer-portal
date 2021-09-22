<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Form\Handler\ConfigHandler;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Provider\CustomerConfigurationFormProvider;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SyncBundle\Content\DataUpdateTopicSender;
use Oro\Bundle\SyncBundle\Content\TagGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles configuration actions for the customer config scope.
 */
class CustomerConfigurationController extends AbstractController
{
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
        $provider = $this->get(CustomerConfigurationFormProvider::class);
        $manager = $this->get(ConfigManager::class);
        $prevScopeId = $manager->getScopeId();
        $manager->setScopeId($entity->getId());

        [$activeGroup, $activeSubGroup] = $provider->chooseActiveGroups($activeGroup, $activeSubGroup);

        $jsTree = $provider->getJsTree();
        $form = false;

        if ($activeSubGroup !== null) {
            $form = $provider->getForm($activeSubGroup);

            if ($this->get(ConfigHandler::class)
                ->setConfigManager($manager)
                ->process($form, $request)
            ) {
                $request->getSession()->getFlashBag()->add(
                    'success',
                    $this->get(TranslatorInterface::class)->trans('oro.config.controller.config.saved.message')
                );

                // outdate content tags, it's only special case for generation that are not covered by NavigationBundle
                $taggableData = ['name' => 'customer_configuration', 'params' => [$activeGroup, $activeSubGroup]];
                $tagGenerator = $this->get(TagGeneratorInterface::class);
                $dataUpdateTopicSender = $this->get(DataUpdateTopicSender::class);

                $dataUpdateTopicSender->send($tagGenerator->generate($taggableData));

                // recreate form to drop values for fields with use_parent_scope_value
                $form = $provider->getForm($activeSubGroup);
                $form->setData($manager->getSettingsByForm($form));
            }
        }
        $manager->setScopeId($prevScopeId);

        return [
            'entity' => $entity,
            'data' => $jsTree,
            'form' => $form ? $form->createView() : null,
            'activeGroup' => $activeGroup,
            'activeSubGroup' => $activeSubGroup,
            'scopeEntity' => $entity,
            'scopeEntityClass' => Customer::class,
            'scopeEntityId' => $entity->getId(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                ConfigManager::class,
                TranslatorInterface::class,
                TagGeneratorInterface::class,
                CustomerConfigurationFormProvider::class,
                ConfigHandler::class,
                DataUpdateTopicSender::class,
            ]
        );
    }
}
