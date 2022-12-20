<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AttachmentBundle\Entity\File as AttachmentFile;
use Oro\Bundle\AttachmentBundle\Manager\FileManager;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\DigitalAssetBundle\Entity\DigitalAsset;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads custom menu item with image on the 2d level.
 */
class LoadCustomMenuUpdateWithImageDemoData extends AbstractMenuUpdateDemoFixture implements DependentFixtureInterface
{
    use UserUtilityTrait;

    private ?FileLocatorInterface $fileLocator = null;

    private ?FileManager $fileManager = null;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->fileLocator = $container->get('file_locator');
        $this->fileManager = $container->get('oro_attachment.file_manager');
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->getFirstUser($manager);

        $menuUpdatesData = $this->getMenuUpdatesData();
        foreach ($menuUpdatesData as $referenceKey => $menuUpdateData) {
            $menuUpdateData['image'] = $this->createImage($manager, $user, $menuUpdateData['image']);
            if (array_key_exists('parentKey', $menuUpdateData)
                && $this->hasReference($menuUpdateData['parentKey'])
            ) {
                $menuUpdateData['parentKey'] = $this->getReference($menuUpdateData['parentKey'])->getKey();
            }

            $menuUpdate = $this->createMenuUpdate($menuUpdateData);
            $manager->persist($menuUpdate);

            $this->setReference($referenceKey, $menuUpdate);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadContentNodeMenuUpdateDemoData::class,
            LoadCategoryMenuUpdateDemoData::class,
        ];
    }

    protected function getMenuUpdatesData()
    {
        $fileName = $this->fileLocator->locate($this->getDataPath());
        $fileName = str_replace('/', DIRECTORY_SEPARATOR, $fileName);

        return Yaml::parse(file_get_contents($fileName));
    }

    protected function getDataPath(): string
    {
        return '@OroCommerceMenuBundle/Migrations/Data/Demo/ORM/data/menuItemsWithImage.yml';
    }

    protected function createMenuUpdate(array $data): MenuUpdate
    {
        $menuUpdate = parent::createMenuUpdate($data);

        $menuUpdate->setImage($data['image']);

        return $menuUpdate;
    }

    protected function createImage(ObjectManager $manager, User $user, string $filename): AttachmentFile
    {
        $imagePath = $this->fileLocator->locate($filename);

        $file = $this->fileManager->createFileEntity($imagePath);
        $file->setOwner($user);
        $manager->persist($file);

        $imageTitle = new LocalizedFallbackValue();
        $imageTitle->setString($filename);
        $manager->persist($imageTitle);

        $digitalAsset = new DigitalAsset();
        $digitalAsset->addTitle($imageTitle)
            ->setSourceFile($file)
            ->setOwner($user)
            ->setOrganization($user->getOrganization());
        $manager->persist($digitalAsset);

        $image = new AttachmentFile();
        $image->setDigitalAsset($digitalAsset);
        $manager->persist($image);
        $manager->flush();

        return $image;
    }
}
