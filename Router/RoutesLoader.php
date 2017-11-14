<?php

namespace Satoripop\CropImagesBundle\Router;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 *
 * @author Mohamed Racem Zouaghi <racem.zouaghi@satoripop.tn>
 *
 */
class RoutesLoader implements LoaderInterface
{
    /**
     * @var boolean
     *
     * Route is loaded
     */
    private $loaded = false;

    /**
     * @var string
     *
     * main path
     */
    private $write_to;

    /**
     * @var string
     *
     * Upload path
     */
    private $upload_dir;

    /**
     * @var string
     *
     * Cache path
     */
    private $cache_dir;

    /**
     * {@inheritdoc}
     */
    public function __construct($write_to, $upload_dir, $cache_dir)
    {
        $this->write_to = $write_to;
        $this->upload_dir = $upload_dir;
        $this->cache_dir = $cache_dir;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if ($this->loaded) {

            throw new \RuntimeException('Do not add this loader twice');
        }

        $routes = new RouteCollection();

        $cache_prefix = str_replace($this->write_to, '', $this->cache_dir);
        $upload_prefix = str_replace($this->write_to, '', $this->upload_dir);

        $routes->add('image_thumb', new Route($cache_prefix . '/{w}x{h}/{filter}__{mode}__{image}', array(
                '_controller' => 'SatoripopCropImagesBundle:Show:thumb',
                'mode' => "center",
            ),
            array('w' => '\d{1,}', 'h' => '\d{1,}', 'image' => '.*', 'mode' => '.*', 'filter' => '.*')
        ));

        $routes->add('image_phantom', new Route('phantom/{w}x{h}/{mode}__{image}', array(
                '_controller' => 'SatoripopCropImagesBundle:Show:phantom',
                'mode' => "center",
            ),
            array('w' => '\d{1,}', 'h' => '\d{1,}', 'image' => '.*', 'mode' => '.*')
        ));

        $routes->add('image_full', new Route($upload_prefix . '/{image}', array(
                '_controller' => 'SatoripopCropImagesBundle:Show:full',
            ),
            array('image' => '.*')
        ));

        $routes->add('image_not_found', new Route($cache_prefix . '/not-found/text/{w}x{h}__{text}.png', array(
                '_controller' => 'SatoripopCropImagesBundle:Show:imagesNotFoundText',
            ),
            array('w' => '\d{1,}', 'h' => '\d{1,}', 'text' => '.*')
        ));

        $routes->add('image_not_found_icon', new Route($cache_prefix . '/not-found/icon/{w}x{h}__{icon}', array(
                '_controller' => 'SatoripopCropImagesBundle:Show:imagesNotFoundIcon',
            ),
            array('w' => '\d{1,}', 'h' => '\d{1,}', 'icon' => '.*')
        ));

        $routes->add('image_not_found_default', new Route($cache_prefix . '/not-found/default/{w}x{h}__{default}', array(
                '_controller' => 'SatoripopCropImagesBundle:Show:imagesNotFoundDefault',
            ),
            array('w' => '\d{1,}', 'h' => '\d{1,}', 'default' => '.*')
        ));

        $this->loaded = true;

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'sp_images' === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getResolver()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {
    }
} 