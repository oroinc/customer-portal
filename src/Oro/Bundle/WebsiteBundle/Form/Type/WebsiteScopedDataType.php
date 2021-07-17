<?php

namespace Oro\Bundle\WebsiteBundle\Form\Type;

use Doctrine\ORM\EntityManager;
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
 * The form type to select websites.
 *
 * @deprecated use Oro\Bundle\ScopeBundle\Form\Type\ScopedDataType instead
 */
class WebsiteScopedDataType extends AbstractType
{
    const NAME = 'oro_website_scoped_data_type';
    const WEBSITE_OPTION = 'website';

    /**
     * @return Website[]
     */
    protected $websites;

    /**
     * @var string
     */
    protected $websiteCLass = 'Oro\Bundle\WebsiteBundle\Entity\Website';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var AclHelper
     */
    protected $aclHelper;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    public function __construct(ManagerRegistry $registry, AclHelper $aclHelper)
    {
        $this->registry = $registry;
        $this->aclHelper = $aclHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            [
                'type',
            ]
        );

        $resolver->setDefaults(
            [
                'preloaded_websites' => [],
                'options' => null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
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

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $formOptions = $form->getConfig()->getOptions();

        $formOptions['options']['data'] = $form->getData();
        $formOptions['options']['ownership_disabled'] = true;

        if (!$data) {
            return;
        }
        foreach ($data as $websiteId => $value) {
            if ($form->has($websiteId)) {
                continue;
            }

            /** @var EntityManager $em */
            $em = $this->registry->getManagerForClass($this->websiteCLass);
            $formOptions['options'][self::WEBSITE_OPTION] = $em
                ->getReference($this->websiteCLass, $websiteId);

            $form->add(
                $websiteId,
                $formOptions['type'],
                $formOptions['options']
            );
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();

        $formOptions = $form->getConfig()->getOptions();

        $formOptions['options']['ownership_disabled'] = true;

        /** @var EntityManager $em */
        $em = $this->registry->getManagerForClass($this->websiteCLass);
        foreach ($event->getData() as $websiteId => $value) {
            $formOptions['options']['data'] = [];

            if (is_array($value)) {
                $formOptions['options']['data'] = $value;
            }

            $formOptions['options'][self::WEBSITE_OPTION] = $em->getReference($this->websiteCLass, $websiteId);

            $form->add(
                $websiteId,
                $formOptions['type'],
                $formOptions['options']
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['websites'] = $this->getWebsites();
    }

    /**
     * @return Website[]
     */
    protected function getWebsites()
    {
        $queryBuilder = $this->registry
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

    /**
     * @param string $websiteCLass
     */
    public function setWebsiteClass($websiteCLass)
    {
        $this->websiteCLass = $websiteCLass;
    }
}
