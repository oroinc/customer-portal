<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ControllerFrontendRestApi;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\GridView;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserGridViewACLData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadGridViewData;
use Oro\Bundle\SecurityBundle\Entity\Permission;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class EntityDataControllerTest extends WebTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->initClient(
            [],
            self::generateBasicAuthHeader(
                LoadCustomerUserGridViewACLData::USER_ACCOUNT_2_ROLE_LOCAL,
                LoadCustomerUserGridViewACLData::USER_ACCOUNT_2_ROLE_LOCAL
            )
        );
        $this->loadFixtures([LoadGridViewData::class]);
    }

    public function testPatchProtectedFrontendEntity(): void
    {
        /** @var GridView $gridView */
        $gridView = $this->getReference(LoadGridViewData::GRID_VIEW_PRIVATE);

        $this->requestPatch(GridView::class, $gridView->getId(), ['name' => 'updated-grid-view']);

        self::assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        self::assertSame('updated-grid-view', $this->refreshEntity(GridView::class, $gridView->getId())->getName());
    }

    public function testPatchProtectedFrontendEntityWithoutEditPermission(): void
    {
        $this->loginUser(LoadCustomerUserGridViewACLData::USER_ACCOUNT_1_ROLE_LOCAL);

        /** @var GridView $gridView */
        $gridView = $this->getReference(LoadGridViewData::GRID_VIEW_PUBLIC);
        $originalName = $gridView->getName();

        $this->requestPatch(GridView::class, $gridView->getId(), ['name' => 'forbidden-grid-view']);

        self::assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_FORBIDDEN);
        self::assertSame($originalName, $this->refreshEntity(GridView::class, $gridView->getId())->getName());
    }

    public function testPatchBackOfficeAclProtectedEntityIsForbidden(): void
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getReference(LoadCustomerUserGridViewACLData::USER_ACCOUNT_2_ROLE_LOCAL);
        $user = $customerUser->getOwner();
        self::assertInstanceOf(User::class, $user);
        $originalFirstName = $user->getFirstName();

        $this->requestPatch(User::class, $user->getId(), ['firstName' => 'forbidden-first-name']);

        self::assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_FORBIDDEN);
        self::assertSame($originalFirstName, $this->refreshEntity(User::class, $user->getId())->getFirstName());
    }

    public function testPatchNotAclProtectedEntityIsForbidden(): void
    {
        /** @var Permission $permission */
        $permission = self::getContainer()->get('doctrine')->getRepository(Permission::class)->findOneBy([]);
        self::assertNotNull($permission);
        $originalName = $permission->getName();

        $this->requestPatch(Permission::class, $permission->getId(), ['name' => 'forbidden-permission-name']);

        self::assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_FORBIDDEN);
        self::assertSame($originalName, $this->refreshEntity(Permission::class, $permission->getId())->getName());
    }

    public function testPatchNotExistingEntityClassReturnsNotFound(): void
    {
        $this->requestPatch('Foo\\Bar\\Entity\\Baz', 1, ['notes' => 'x']);

        $response = self::getJsonResponseContent($this->client->getResponse(), Response::HTTP_NOT_FOUND);
        self::assertSame(Response::HTTP_NOT_FOUND, $response['code']);
    }

    public function testPatchWithNonNumericIdReturnsNotFound(): void
    {
        $this->requestPatch(GridView::class, 'abc', ['name' => 'invalid-grid-view']);

        $response = self::getJsonResponseContent($this->client->getResponse(), Response::HTTP_NOT_FOUND);
        self::assertSame(Response::HTTP_NOT_FOUND, $response['code']);
    }

    public function testPatchUnknownFieldReturnsNotFound(): void
    {
        /** @var GridView $gridView */
        $gridView = $this->getReference(LoadGridViewData::GRID_VIEW_PRIVATE);
        $originalName = $gridView->getName();

        $this->requestPatch(GridView::class, $gridView->getId(), ['nosuchfield' => 'x']);

        $response = self::getJsonResponseContent($this->client->getResponse(), Response::HTTP_NOT_FOUND);
        self::assertSame(Response::HTTP_NOT_FOUND, $response['code']);
        self::assertSame($originalName, $this->refreshEntity(GridView::class, $gridView->getId())->getName());
    }

    private function requestPatch(string $className, mixed $id, array $data): void
    {
        $className = self::getContainer()
            ->get('oro_entity.entity_class_name_helper')
            ->getUrlSafeClassName($className);

        $this->ajaxRequest(
            'PATCH',
            $this->getUrl('oro_api_frontend_patch_entity_data', ['className' => $className, 'id' => $id]),
            [],
            [],
            [],
            json_encode($data, JSON_THROW_ON_ERROR)
        );
    }

    private function refreshEntity(string $className, mixed $id): object
    {
        $entityManager = self::getContainer()->get('doctrine')->getManagerForClass($className);
        $entityManager->clear();

        return $entityManager->getRepository($className)->find($id);
    }
}
