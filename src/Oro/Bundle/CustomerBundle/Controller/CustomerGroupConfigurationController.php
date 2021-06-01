<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Form\Handler\ConfigHandler;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Provider\CustomerGroupConfigurationFormProvider;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SyncBundle\Content\DataUpdateTopicSender;
use Oro\Bundle\SyncBundle\Content\TagGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles configuration actions for the customer group config scope.
 */
class CustomerGroupConfigurationController extends AbstractController
{
    /**
     * @Route(
     *      "/customerGroup/{id}/{activeGroup}/{activeSubGroup}",
     *      name="oro_customer_group_config",
     *      requirements={"id"="\d+"},
     *      defaults={"activeGroup" = null, "activeSubGroup" = null}
     * )
     * @Template()
     * @AclAncestor("oro_customer_customer_group_update")
     *
     * @param Request $request
     * @param CustomerGroup $entity
     * @param string|null $activeGroup
     * @param string|null $activeSubGroup
     * @return array
     */
    public function customerGroupConfigAction(
        Request $request,
        CustomerGroup $entity,
        ?string $activeGroup = null,
        ?string $activeSubGroup = null
    ): array {
        $provider = $this->get(CustomerGroupConfigurationFormProvider::class);
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
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get(TranslatorInterface::class)->trans('oro.config.controller.config.saved.message')
                );

                $taggableData = ['name' => 'customer_group_configuration', 'params' => [$activeGroup, $activeSubGroup]];
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
            'scopeEntityClass' => CustomerGroup::class,
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
                ConfigHandler::class,
                DataUpdateTopicSender::class,
                CustomerGroupConfigurationFormProvider::class
            ]
        );
    }
}
