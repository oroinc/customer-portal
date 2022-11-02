<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Controller\Workflow;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\FrontendBundle\Tests\Functional\DataFixtures\LoadWorkflowDefinitions;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Entity\WorkflowAwareEntity;
use Oro\Bundle\TestFrameworkBundle\Form\Type\WorkflowAwareEntityType;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Symfony\Component\DomCrawler\Crawler;

class WidgetControllerTest extends WebTestCase
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var WorkflowManager */
    private $workflowManager;

    /** @var WorkflowAwareEntity */
    private $entity;

    protected function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadCustomerUserData::AUTH_USER, LoadCustomerUserData::AUTH_PW)
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadWorkflowDefinitions::class]);

        $this->entityManager = $this->client->getContainer()->get('doctrine')
            ->getManagerForClass(WorkflowAwareEntity::class);
        $this->workflowManager = $this->client->getContainer()->get('oro_workflow.manager');
        $this->workflowManager->activateWorkflow(LoadWorkflowDefinitions::COMMERCE_WORKFLOW_FORMS);
        $this->entity = $this->createNewEntity();
    }

    public function testStartTransitionFormAction()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_frontend_workflow_widget_start_transition_form',
                [
                    'workflowName' => LoadWorkflowDefinitions::COMMERCE_WORKFLOW_FORMS,
                    'transitionName' => LoadWorkflowDefinitions::COMMERCE_WORKFLOW_FORMS_START_TRANSITION,
                    'entityId' => $this->entity->getId(),
                    '_widgetContainer' => 'dialog',
                    '_wid' => 'test-uuid'
                ]
            )
        );
        $response = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($response, 200);
        $html = $crawler->html();

        $this->assertNotEmpty($html);
        self::assertStringContainsString('oro.testframework.workflowawareentity.name.label', $html);

        $workflowItem = new WorkflowItem();
        $workflowItem->setEntityId($this->entity->getId());
        $workflowItem->setWorkflowName(LoadWorkflowDefinitions::COMMERCE_WORKFLOW_FORMS);

        $this->assertTransitionFormSubmit(
            $crawler,
            $workflowItem,
            'data_value_one',
            [WorkflowAwareEntityType::NAME => ['name' => 'initial_name']]
        );
    }

    public function testTransitionFormAction()
    {
        $this->getContainer()->get('oro_workflow.manager')->startWorkflow(
            LoadWorkflowDefinitions::COMMERCE_WORKFLOW_FORMS,
            $this->entity,
            LoadWorkflowDefinitions::COMMERCE_WORKFLOW_FORMS_START_TRANSITION
        );

        $workflowItem = $this->getWorkflowItem($this->entity, LoadWorkflowDefinitions::COMMERCE_WORKFLOW_FORMS);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_frontend_workflow_widget_transition_form',
                [
                    'workflowItemId' => $workflowItem->getId(),
                    'transitionName' => LoadWorkflowDefinitions::COMMERCE_WORKFLOW_FORMS_TRANSITION,
                    '_widgetContainer' => 'dialog',
                    '_wid' => 'test-uuid'
                ]
            )
        );
        $response = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($response, 200);
        $html = $crawler->html();
        $this->assertNotEmpty($html);
        self::assertStringContainsString('oro.testframework.workflowawareentity.name.label', $html);
        $this->assertTransitionFormSubmit(
            $crawler,
            $workflowItem,
            'data_value_one',
            [WorkflowAwareEntityType::NAME => ['name' => 'custom_name']]
        );
    }

    private function assertTransitionFormSubmit(
        Crawler $crawler,
        WorkflowItem $workflowItem,
        string $dataAttribute,
        array $data = []
    ): void {
        $form = $crawler->selectButton('Submit')->form($data);

        $this->client->followRedirects(true);
        $this->client->submit($form);
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $workflowItemNew = $this->getWorkflowItem($this->entity, $workflowItem->getWorkflowName());

        if ($workflowItem->getId()) {
            $this->assertSame($workflowItem->getId(), $workflowItemNew->getId());
        }
        $this->assertInstanceOf(WorkflowAwareEntity::class, $workflowItemNew->getData()->get($dataAttribute));
    }

    private function createNewEntity(): WorkflowAwareEntity
    {
        $testEntity = new WorkflowAwareEntity();
        $testEntity->setName('test_' . uniqid('test', true));
        $this->entityManager->persist($testEntity);
        $this->entityManager->flush($testEntity);

        return $testEntity;
    }

    private function getWorkflowItem(WorkflowAwareEntity $entity, string $workflowName): WorkflowItem
    {
        return $this->getContainer()->get('oro_workflow.manager')
            ->getWorkflowItem($entity, $workflowName);
    }
}
