<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class LoadWorkflowDefinitions extends AbstractFixture implements ContainerAwareInterface
{
    const COMMERCE_WORKFLOW_FORMS = 'commerce_workflow_with_form_configuration';
    const COMMERCE_WORKFLOW_FORMS_START_TRANSITION = 'start_transition';
    const COMMERCE_WORKFLOW_FORMS_TRANSITION = 'transition_1';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $hasDefinitions = false;

        $listConfiguration = $this->container->get('oro_workflow.configuration.config.workflow_list');
        $configurationBuilder = $this->container->get('oro_workflow.configuration.builder.workflow_definition');

        $workflowConfiguration = $this->getWorkflowConfiguration();
        $workflowConfiguration = $listConfiguration->processConfiguration($workflowConfiguration);
        $workflowDefinitions = $configurationBuilder->buildFromConfiguration($workflowConfiguration);

        foreach ($workflowDefinitions as $workflowDefinition) {
            if ($manager->getRepository(WorkflowDefinition::class)->find($workflowDefinition->getName())) {
                continue;
            }
            $manager->persist($workflowDefinition);
            $this->addReference('oro_frontend.workflow.' . $workflowDefinition->getName(), $workflowDefinition);
            $hasDefinitions = true;
        }

        if ($hasDefinitions) {
            $manager->flush();
        }
    }

    /**
     * @return array
     */
    protected function getWorkflowConfiguration()
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/config/oro/workflows.yml')) ?: [];
    }
}
