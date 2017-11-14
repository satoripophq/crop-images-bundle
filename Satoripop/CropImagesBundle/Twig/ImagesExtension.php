<?php

namespace Satoripop\CropImagesBundle\Twig;

use Satoripop\CropImagesBundle\Entity\Image;
use Satoripop\CropImagesBundle\Services\ImageProcessor;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\TranslatorInterface;

/**
 *
 * @author Mohamed Racem Zouaghi <racem.zouaghi@satoripop.tn>
 *
 */
class ImagesExtension extends \Twig_Extension
{

    /**
     * @var string $cache_dir
     */
    private $cache_dir;

    /**
     * @var Router $router
     */
    private $router;

    /**
     * @var TranslatorInterface $trans
     */
    private $trans;

    /**
     * @var ImageProcessor $processor
     */
    private $processor;

    /**
     * @var string $write_to
     */
    private $write_to;

    /**
     * @param string $write_to
     * @param string $cache_dir
     * @param Router $router
     * @param TranslatorInterface $trans
     * @param ImageProcessor $processor
     */
    public function __construct($write_to, $cache_dir, Router $router, TranslatorInterface $trans, ImageProcessor $processor)
    {
        $this->cache_dir = $cache_dir;
        $this->router = $router;
        $this->trans = $trans;
        $this->processor = $processor;
        $this->write_to = $write_to;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            'thumb' => new \Twig_Filter_Method($this, 'getThumbUri'),
            'base64_thumb' => new \Twig_Filter_Method($this, 'getThumbBase64'),
            'full_image' => new \Twig_Filter_Method($this, 'getImageUri'),
            'file_size' => new \Twig_Filter_Method($this, 'formatSizeUnits'),
        );
    }

    /**
     * @param Image|string|null $image
     * @param array $size
     * @param array $options
     * @return string
     */
    public function getThumbBase64($image, $size, $options = array())
    {
        $path = $this->write_to . '/' . $this->getThumbUri($image, $size, $options);
        $type = pathinfo($path, PATHINFO_EXTENSION);
        return 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path));
    }

    /**
     * @param Image|string|null $image
     * @param array $size
     * @param array $options
     * @return string
     */
    public function getThumbUri($image, $size, $options = array())
    {
        if (!is_array($size)) {
            if (preg_match("/\d*x\d*/i", $size)) {
                $size = explode('x', $size);
                if ($image) {
                    $original = array($image->getWidth(), $image->getHeight());
                    foreach ($size as $i => $value) {
                        if (!(int)$value) {
                            $ratio = $original[$i] / $original[($i + 1) % 2];
                            $size[$i] = round($size[($i + 1) % 2] * $ratio);
                        }
                    }
                } else {
                    foreach ($size as $i => $value) {
                        if (!(int)$value) {
                            $size[$i] = $size[($i + 1) % 2];
                        }
                    }
                }
            } else {
                return $this->getImageUri($image);
            }
        } elseif (!count($size)) {
            return $this->getImageUri($image);
        }

        if ($image && $image instanceof Image) {
            $mode = isset($options['mode']) ? $options['mode'] : null;
            $filter = isset($options['filter']) ? $options['filter'] : null;
            if ($image->getPhantomPath()) {
                return $this->router->generate('image_phantom', array('mode' => $mode, 'w' => reset($size), 'h' => end($size), 'image' => $image->getPhantomPath()));
            }
            try {
                $this->processor->generateThumb(reset($size), end($size), $image->getPath(), $mode, true, false, $filter);
                return str_replace($this->router->getContext()->getBaseUrl(), '', $this->router->generate('image_thumb', array('filter' => $filter, 'mode' => $mode, 'w' => reset($size), 'h' => end($size), 'image' => $image->getPath())));
            } catch (\Exception $e) {
                return str_replace($this->router->getContext()->getBaseUrl(), '', $this->generateNotFoundLink($size, $options));
            }
        } else {
            return str_replace($this->router->getContext()->getBaseUrl(), '', $this->generateNotFoundLink($size, $options));
        }
    }

    /**
     * @param Image $image
     * @return mixed
     */
    public function getImageUri($image)
    {
        if ($image) {
            if ($image instanceof Image) {
                return str_replace($this->router->getContext()->getBaseUrl(), '', $this->router->generate('image_full', array('image' => $image->getPath())));
            } elseif (is_string($image)) {
                return $image;
            }
        } else {
            return str_replace($this->router->getContext()->getBaseUrl(), '', $this->generateNotFoundLink(array(256, 256)));
        }
    }

    /**
     * @param $size
     * @param array $options
     * @return string
     */
    private function generateNotFoundLink($size, $options = array())
    {
        $this->processor->processOptions($options);
        switch (true) {
            case isset($options["icon"]):
                return $this->router->generate('image_not_found_icon', array('w' => reset($size), 'h' => end($size), 'icon' => str_replace("/", "__", $options["icon"])));
                break;
            case isset($options["default"]):
                return $this->router->generate('image_not_found_default', array('w' => reset($size), 'h' => end($size), 'default' => str_replace("/", "__", $options["default"])));
                break;
            case isset($options["text"]):
                return $this->router->generate('image_not_found', array('w' => reset($size), 'h' => end($size), 'text' => str_replace(' ', '__', strtolower($this->trans->trans($options["text"])))));
                break;
            default:
                return $this->router->generate('image_not_found', array('w' => reset($size), 'h' => end($size), 'text' => str_replace(' ', '__', strtolower($this->trans->trans('image.not_found', array(), 'SatoripopCropImagesBundle')))));
                break;
        }
    }

    /**
     * @param integer $bytes
     * @return string
     */
    public function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'image_extension';
    }

}
