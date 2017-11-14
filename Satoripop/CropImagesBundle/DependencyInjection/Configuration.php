<?php

namespace Satoripop\CropImagesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @author Mohamed Racem Zouaghi <racem.zouaghi@satoripop.tn>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('satoripop_images');
        $rootNode
            ->children()
                ->scalarNode('image_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('quality')->defaultValue('75')->end()
                ->scalarNode('write_to')->defaultValue('%kernel.root_dir%/../web')->end()
                ->scalarNode('connection')->defaultValue('default')->end()
                ->scalarNode('upload_path')->defaultValue('uploads')->end()
                ->scalarNode('cache_path')->defaultValue('cache')->end()
                ->scalarNode('not_found_icon')->defaultNull()->end()
                ->scalarNode('not_found_default')->defaultNull()->end()
                ->scalarNode('not_found_text')->defaultNull()->end()
                ->scalarNode('not_found_color')->defaultValue('#aaaaaa')->end()
                ->scalarNode('not_found_bg_color')->defaultValue('#dddddd')->end()
                ->booleanNode('generate_thumbs')->defaultFalse()->end()
                ->arrayNode('thumbs')->prototype('variable')->end()->end()
                ->arrayNode('filters')->prototype('variable')->end()->end()
            ->end()
            ->validate()
                ->ifTrue(function($v){foreach($v['thumbs'] as $key=>$size){if(!(is_array($size) || is_array(explode('x',$size)))){echo $key;return true;}};return false;})
                ->thenInvalid('The thumbnails sizes are formatted incorrectly.')
            ->end()
            ->validate()
                ->ifTrue(function($v){return $v['generate_thumbs'] && empty($v['thumbs']);})
                ->thenInvalid('You need to specify the thumbnails sizes to be generated.')
            ->end();
        return $treeBuilder;
    }
}
