<?php

namespace Satoripop\CropImagesBundle\Form;

use Satoripop\CropImagesBundle\Entity\Image;
use Satoripop\CropImagesBundle\Form\Transformer\ImageCollectionTransformer;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;

class GalleryType extends FileType
{
    /**
     * @var string $class
     */
    private $class;

    /**
     * @var EntityManager $em
     */
    private $em;

    public function __construct($image_class, EntityManager $em)
    {
        $this->class = $image_class;
        $this->em = $em;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $this->onPreSetData($event, $options);
        });
        $transformer = new ImageCollectionTransformer($this->class, $builder->getForm()->getData());
        $builder
            ->addModelTransformer($transformer);
    }

    public function onPreSetData(FormEvent $event, array $options)
    {
        unset($options['multiple']);
        unset($options['data_class']);
        $form = $event->getForm();
        $data = $event->getData() ? $event->getData() : array();
        foreach ($data as $key => $value) {
            if ($value instanceof Image) {
                $form->add($key, 'sp_image', $options);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(\Symfony\Component\Form\FormView $view, \Symfony\Component\Form\FormInterface $form, array $options)
    {
        if ($options['multiple']) {
            $view->vars['full_name'] .= '[]';
            $view->vars['attr']['multiple'] = 'multiple';
        }

        $view->vars = array_replace($view->vars, array(
            'type' => 'file'
        ));
        $view->vars['thumb_size'] = $options['thumbnail'];
        $view->vars['thumb_options'] = $options['thumb_options'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'SatoripopCropImagesBundle',
            'required' => false,
            'empty_data' => null,
            'multiple' => true,
            'thumbnail' => array(200, 200),
            'thumb_options' => array()
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sp_gallery';
    }
}