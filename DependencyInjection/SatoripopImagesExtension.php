<?php

namespace Satoripop\CropImagesBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author Mohamed Racem Zouaghi <racem.zouaghi@satoripop.tn>
 */
class SatoripopImagesExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('sp_images.image_class', $config['image_class']);
        $container->setParameter('sp_images.connection', $config['connection']);
        $container->setParameter('sp_images.quality', $config['quality']);
        $container->setParameter('sp_images.write_to', $config['write_to']);
        $container->setParameter('sp_images.upload_path', $config['write_to'] . '/' . $config['upload_path']);
        $container->setParameter('sp_images.cache_path', $config['write_to'] . '/' . $config['cache_path']);
        $container->setParameter('sp_images.generate_thumbs', $config['generate_thumbs']);
        $container->setParameter('sp_images.thumbs', $config['thumbs']);
        $container->setParameter('sp_images.not_found_icon', $config['not_found_icon']);
        $container->setParameter('sp_images.not_found_default', $config['not_found_default']);
        $container->setParameter('sp_images.not_found_color', $config['not_found_color']);
        $container->setParameter('sp_images.not_found_text', $config['not_found_text']);
        $container->setParameter('sp_images.not_found_bg_color', $config['not_found_bg_color']);
        $container->setParameter('sp_images.filters', $config['filters']);
        $container->setParameter('twig.form.resources', array_merge(
            $container->getParameter('twig.form.resources'),
            array(
                'SatoripopCropImagesBundle:Form:sp_image-prototype.html.twig',
                'SatoripopCropImagesBundle:Form:sp_gallery-prototype.html.twig',
            )
        ));
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('satoripop_images.xml');
    }
}
