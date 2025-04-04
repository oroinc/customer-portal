<?php

namespace Oro\Bundle\WebsiteBundle\Form\Type;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for website scoped data.
 */
class WebsiteScopedDataType extends AbstractType
{
    public const WEBSITE_OPTION = 'website';

    public function __construct(
        private ManagerRegistry $doctrine,
        private AclHelper $aclHelper
    ) {
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_website_scoped_data_type';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['type']);
        $resolver->setDefaults([
            'preloaded_websites' => [],
            'options' => null
        ]);
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $loadedWebsites = !empty($options['preloaded_websites'])
            ? $options['preloaded_websites']
            : $this->getWebsites();

        $options['options']['data'] = $options['data'];
        $options['options']['ownership_disabled'] = true;

        foreach ($loadedWebsites as $website) {
            $options['options'][self::WEBSITE_OPTION] = $website;
            $builder->add(
                $website->getId(),
                $options['type'],
                $options['options']
            );
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
    }

    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['websites'] = $this->getWebsites();
    }

    public function preSubmit(FormEvent $event): void
    {
        $data = $event->getData();

        if (!$data) {
            return;
        }

        $form = $event->getForm();
        $formOptions = $form->getConfig()->getOptions();
        $formOptions['options']['data'] = $form->getData();
        $formOptions['options']['ownership_disabled'] = true;

        foreach (array_keys($data) as $websiteId) {
            if ($form->has($websiteId)) {
                continue;
            }

            $em = $this->doctrine->getManagerForClass(Website::class);
            $formOptions['options'][self::WEBSITE_OPTION] = $em->getReference(Website::class, $websiteId);

            $form->add($websiteId, $formOptions['type'], $formOptions['options']);
        }
    }

    public function preSetData(FormEvent $event): void
    {
        $form = $event->getForm();

        $formOptions = $form->getConfig()->getOptions();
        $formOptions['options']['ownership_disabled'] = true;

        $em = $this->doctrine->getManagerForClass(Website::class);
        foreach ($event->getData() as $websiteId => $value) {
            $formOptions['options']['data'] = [];
            if (\is_array($value)) {
                $formOptions['options']['data'] = $value;
            }
            $formOptions['options'][self::WEBSITE_OPTION] = $em->getReference(Website::class, $websiteId);

            $form->add($websiteId, $formOptions['type'], $formOptions['options']);
        }
    }

    /**
     * @return Website[]
     */
    private function getWebsites(): array
    {
        $queryBuilder = $this->doctrine
            ->getRepository(Website::class)
            ->createQueryBuilder('website')
            ->addOrderBy('website.id', 'ASC');

        $websites = $this->aclHelper->apply($queryBuilder)->getResult();
        $result = [];

        foreach ($websites as $website) {
            $result[$website->getId()] = $website;
        }

        return $result;
    }
}
