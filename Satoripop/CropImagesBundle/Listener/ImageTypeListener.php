<?php

namespace Satoripop\CropImagesBundle\Listener;

use Satoripop\CropImagesBundle\Services\PathMaker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormEvent;
use Satoripop\CropImagesBundle\Entity\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageTypeListener implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $factory;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var PathMaker $pm
     */
    private $pm;

    /**
     * @var string $cache_dir
     */
    private $cache_dir;

    /**
     * @param FormFactoryInterface $factory
     * @param EntityManager $em
     * @param string $cache_dir
     * @param PathMaker $pm
     * @internal param FormFactoryInterface $factory
     */
    public function __construct(FormFactoryInterface $factory, EntityManager $em, $cache_dir, PathMaker $pm)
    {
        $this->factory = $factory;
        $this->em = $em;
        $this->cache_dir = $cache_dir . "/temp_buffer";
        $this->pm = $pm;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => 'onSubmit',
        );
    }

    /**
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        /**
         * @var array $data
         */
        $data = $event->getData();

        /**
         * @var UploadedFile $file
         */
        $file = null;
        if (isset($data['file']) && $file = $data['file']) {
            unset($data['file']);
            $prefix = uniqid(time()) . "_";
            if ($this->pm->makePath($this->cache_dir)) {
                $file->move($this->cache_dir, $prefix . $file->getClientOriginalName());
                $data['phantomPath'] = $prefix . $file->getClientOriginalName();
                $event->setData($data);
            }
        }
    }

}