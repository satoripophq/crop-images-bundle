<?php

namespace Satoripop\CropImagesBundle\Form;

use Satoripop\CropImagesBundle\Form\Transformer\ImageTransformer;
use Satoripop\CropImagesBundle\Listener\ImageTypeListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityManager;

/**
 *
 * @author Mohamed Racem Zouaghi <racem.zouaghi@satoripop.tn>
 *
 */
class ImageType extends AbstractType
{
    /**
     * @var string $class
     */
    private $class;

    /**
     * @var EntityManager $em
     */
    private $em;
    /**
     * @var ImageTypeListener $imageTypeListener
     */
    private $imageTypeListener;

    /**
     * @param $image_class
     * @param EntityManager $em
     * @param ImageTypeListener $imageTypeListener
     */
    public function __construct($image_class, EntityManager $em, ImageTypeListener $imageTypeListener)
    {
        $this->class = $image_class;
        $this->em = $em;
        $this->imageTypeListener = $imageTypeListener;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', 'file', array('label' => 'form.file', 'translation_domain' => 'SatoripopCropImagesBundle',));
        $builder->add('file_data', 'hidden', array('label' => false, 'translation_domain' => 'SatoripopCropImagesBundle',));
        $builder->add('phantomPath', 'hidden', array('label' => false));
        $builder->addModelTransformer(new ImageTransformer($this->em));
        $builder->addEventSubscriber($this->imageTypeListener);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(\Symfony\Component\Form\FormView $view, \Symfony\Component\Form\FormInterface $form, array $options)
    {
        $view->vars['ratio'] = $options['ratio'];
        $view->vars['thumb_size'] = $options['thumbnail'];
        $view->vars['thumb_options'] = $options['thumb_options'];
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
            'ratio' => 1,
            'thumbnail' => array(200, 200),
            'thumb_options' => array()
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sp_image';
    }
}
