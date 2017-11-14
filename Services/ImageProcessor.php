<?php

namespace Satoripop\CropImagesBundle\Services;

use Imagine\Filter\FilterInterface;
use Imagine\Filter\Transformation;
use Imagine\Gd\Font;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class ImageProcessor
{

    /**
     * @var string $cache_path
     */
    private $cache_path;

    /**
     * @var string $upload_path
     */
    private $upload_path;

    /**
     * @var string $write_to
     */
    private $write_to;

    /**
     * @var string $not_found_bg_color
     */
    private $not_found_bg_color;

    /**
     * @var string $not_found_color
     */
    private $not_found_color;

    /**
     * @var string $not_found_icon
     */
    private $not_found_icon;

    /**
     * @var string $not_found_text
     */
    private $not_found_text;

    /**
     * @var string $not_found_default
     */
    private $not_found_default;
    /**
     * @var integer $quality
     */
    private $quality;

    /**
     * @var PathMaker $path_maker
     */
    private $path_maker;

    /**
     * @var array $filters
     */
    private $filters;

    /**
     * @param string $cache_path
     * @param string $upload_path
     * @param string $write_to
     * @param string $not_found_bg_color
     * @param string $not_found_color
     * @param string $not_found_icon
     * @param string $not_found_default
     * @param string $not_found_text
     * @param integer $quality
     * @param PathMaker $path_maker
     * @param array $filters
     */
    public function __construct($cache_path, $upload_path, $write_to, $not_found_bg_color, $not_found_color, $not_found_icon, $not_found_default, $not_found_text, $quality, PathMaker $path_maker, array $filters = array())
    {
        $this->cache_path = $cache_path;
        $this->upload_path = $upload_path;
        $this->write_to = $write_to;
        $this->not_found_bg_color = $not_found_bg_color;
        $this->not_found_color = $not_found_color;
        $this->not_found_icon = $not_found_icon;
        $this->not_found_default = $not_found_default;
        $this->not_found_text = $not_found_text;
        $this->path_maker = $path_maker;
        $this->quality = $quality;
        $this->filters = $filters;
    }

    /**
     * @param integer $w
     * @param integer $h
     * @param string $image
     * @param array|string $mode
     * @param boolean $cache
     * @param boolean $full_path
     *
     * @return \Imagine\Gd\Image|ImageInterface|\Imagine\Image\ManipulatorInterface
     */
    public function generateThumb($w, $h, $image, $mode = "center", $cache = true, $full_path = false, $filter = null)
    {
        set_time_limit(0);
        $imagine = new Imagine();
        try {
            $file = $imagine->open($this->cache_path . '/' . $w . 'x' . $h . '/' . $filter . '__' . $mode . '__' . $image);
        } catch (\Exception $e) {

            $filename = $full_path ? $image : $this->upload_path . '/' . $image;
            $transformation = new Transformation();
            $imagine = new Imagine();
            $file = $imagine->open($filename);
            if ($filter && isset($this->filters[$filter])) {
                /**
                 * @var FilterInterface $filter
                 */
                $filterO = new $this->filters[$filter]($imagine);
                $file = $filterO->apply($file);
                $savename = $image . ".png";
            } else {
                $savename = $image;
            }
            if ($mode) {
                $mode_val = array(
                    'vertical' => 'center',
                    'horizontal' => 'center',
                );
                if (is_string($mode)) {
                    if (in_array($mode, array('top', 'down'))) {
                        $mode_val['vertical'] = $mode;
                    } elseif (in_array($mode, array('left', 'right'))) {
                        $mode_val['horizontal'] = $mode;
                    }
                } elseif (is_array($mode)) {
                    foreach ($mode as $key => $val) {
                        $mode_val[$key] = $val;
                    }
                }
                $ratio = $w / $h;
                if ($file->getSize()->getHeight() * $ratio > $file->getSize()->getWidth()) {
                    $width = $file->getSize()->getWidth();
                    $height = $file->getSize()->getWidth() / $ratio;
                } else {
                    $width = $file->getSize()->getHeight() * $ratio;
                    $height = $file->getSize()->getHeight();
                }
                switch ($mode_val['vertical']) {
                    case 'top':
                        $y = 0;
                        break;
                    case 'down':
                        $y = $file->getSize()->getHeight() - $height;
                        break;
                    default:
                        $y = ($file->getSize()->getHeight() - $height) / 2;
                        break;
                }
                switch ($mode_val['horizontal']) {
                    case 'left':
                        $x = 0;
                        break;
                    case 'right':
                        $x = $file->getSize()->getWidth() - $width;
                        break;
                    default:
                        $x = ($file->getSize()->getWidth() - $width) / 2;
                        break;
                }
                $transformation->crop(new Point(ceil($x), ceil($y)), new Box(ceil($width), ceil($height)));
            }
            $transformation->thumbnail(new Box($w, $h), ImageInterface::THUMBNAIL_OUTBOUND);
            if ($this->path_maker->makePath($this->cache_path . '/' . $w . 'x' . $h)) {
                $file = $transformation->apply($file);
                if ($cache) {
                    $file = $file->save(realpath($this->cache_path . '/' . $w . 'x' . $h) . '/' . $filter . '__' . $mode . '__' . $savename, array('quality' => $this->quality));
                    rename(realpath($this->cache_path . '/' . $w . 'x' . $h) . '/' . $filter . '__' . $mode . '__' . $savename, realpath($this->cache_path . '/' . $w . 'x' . $h) . '/' . $filter . '__' . $mode . '__' . $image);
                }
            }
        }
        return $file;
    }

    /**
     * @param integer $w
     * @param integer $h
     * @param string $image
     * @param array|null $mode
     * @param bool $cache
     * @param bool $full_path
     *
     * @return Response
     */
    public function getThumb($w, $h, $image, $mode = null, $cache = true, $full_path = false, $filter = null)
    {
        return new Response($this->generateThumb($w, $h, $image, $mode, $cache, $full_path, $filter), 200, array(
                'Content-type' => mime_content_type($full_path ? $image : $this->cache_path . '/' . $w . 'x' . $h . '/' . $filter . '__' . $mode . '__' . $image)
            )
        );
    }

    /**
     * @param string $image
     * @return \Imagine\Gd\Image|ImageInterface
     */
    public function generateImage($image)
    {
        $imagine = new Imagine();
        try {
            $file = $imagine->open($this->upload_path . '/' . $image);
            return $file;
        } catch (\Exception $e) {
            throw new NotFoundResourceException('The requested image was not found', 404);
        }
    }

    /**
     * @param string $image
     * @return Response
     */
    public function getImage($image)
    {
        return new Response($this->generateImage($image), 200, array(
                'Content-type' => mime_content_type($this->upload_path . '/' . $image)
            )
        );
    }

    /**
     * @param integer $w
     * @param integer $h
     * @param string $default
     * @return ImageInterface|mixed
     */
    public function generateNotFoundDefault($w, $h, $default)
    {
        $imagine = new Imagine();
        try {
            $default = $imagine->open($this->write_to . '/' . $default);
            $transformation = new Transformation();
            $image = $transformation->thumbnail(new Box($w, $h), ImageInterface::THUMBNAIL_OUTBOUND)->apply($default);
            return $image;
        } catch (\Exception $e) {
            throw new NotFoundResourceException($e->getMessage(), 500);
        }
    }

    /**
     * @param integer $w
     * @param integer $h
     * @param string $default
     * @return Response
     */
    public function getNotFoundDefault($w, $h, $default)
    {
        $text = str_replace("/", "__", $default);
        return $this->returnImage($this->generateNotFoundDefault($w, $h, $default), $w, $h, $this->cache_path . '/not-found/default/', $text);
    }

    /**
     * @param integer $w
     * @param integer $h
     * @param string $icon
     * @return \Imagine\Gd\Image|ImageInterface
     */
    public function generateNotFoundIcon($w, $h, $icon)
    {
        $text = str_replace("/", "__", $icon);
        $imagine = new Imagine();
        $palette = new RGB();
        $image = $imagine->create(new Box($w, $h), $palette->color($this->not_found_bg_color, 100));
        if ($icon === null) {
            $icon = $this->not_found_icon;
        }
        try {
            $icon = $imagine->open($this->write_to . '/' . $icon);
            $image = $imagine->open($this->cache_path . '/not-found/icon/' . $w . 'x' . $h . '_' . $text . '.png');
            return $image;
        } catch (\Exception $e) {
            try {
                $transformation = new Transformation();
                $transformation->thumbnail(new Box(round($w / 5 * 3), round($h / 5 * 3)), ImageInterface::THUMBNAIL_INSET);
                $icon = $transformation->apply($icon);
                $icon->effects()->colorize($palette->color($this->not_found_color, 100));
                $x = ceil(($w - $icon->getSize()->getWidth()) / 2);
                $y = ceil(($h - $icon->getSize()->getHeight()) / 2);
                $image->paste($icon, new Point($x, $y));
                return $image;
            } catch (\Exception $e) {
                throw new NotFoundResourceException($e->getMessage(), 500);
            }
        }

    }

    /**
     * @param integer $w
     * @param integer $h
     * @param string $icon
     * @return Response
     */
    public function getNotFoundIcon($w, $h, $icon)
    {
        $text = str_replace("/", "__", $icon);
        return $this->returnImage($this->generateNotFoundIcon($w, $h, $icon), $w, $h, $this->cache_path . '/not-found/icon/', $text . '.png');
    }

    /**
     * @param integer $w
     * @param integer $h
     * @param string $text
     * @return \Imagine\Gd\Image|ImageInterface
     */
    public function generateNotFoundText($w, $h, $text)
    {
        $imagine = new Imagine();
        $palette = new RGB();
        try {
            $image = $imagine->open($this->cache_path . '/not-found/text/' . $w . 'x' . $h . '_' . strtolower(str_replace(' ', '__', $text)) . '.png');
            return $image;
        } catch (\Exception $e) {
            try {
                $image = $imagine->create(new Box($w, $h), $palette->color($this->not_found_bg_color, 100));
                $size = min(ceil($w / 14), ceil($h / 14));
                $font = new Font(realpath(__DIR__ . '/../Resources/font/OpenSansRegular.ttf'), $size, $palette->color($this->not_found_color, 100));
                $content = ucwords(trim(str_replace('_', ' ', $text)));
                $tb = $font->box($content);
                $x = ceil(($w - $tb->getWidth()) / 2);
                $y = ceil(($h - $tb->getHeight()) / 2);
                $image->draw()->text($content, $font, new Point($x, $y));
                return $image;
            } catch (\Exception $e) {
                throw new NotFoundResourceException($e->getMessage(), 500);
            }
        }
    }

    /**
     * @param integer $w
     * @param integer $h
     * @param string $text
     * @return Response
     */
    public function getNotFoundText($w, $h, $text)
    {
        return $this->returnImage($this->generateNotFoundText($w, $h, $text), $w, $h, $this->cache_path . '/not-found/text/', strtolower(str_replace(' ', '__', $text)) . '.png');
    }

    /**
     * @param array $options
     */
    public function processOptions(array &$options)
    {
        if (empty($options)) {
            if ($this->not_found_icon) {
                $options['icon'] = $this->not_found_icon;
            } elseif ($this->not_found_default) {
                $options['default'] = $this->not_found_icon;
            } elseif ($this->not_found_text) {
                $options['text'] = $this->not_found_icon;
            }
        }
    }

    /**
     * @param ImageInterface $image
     * @param integer $w
     * @param integer $h
     * @param string $path
     * @param string $filename
     * @return Response
     */
    private function returnImage(ImageInterface $image, $w, $h, $path, $filename)
    {
        if ($this->path_maker->makePath($path)) {
            $image->save(realpath($path) . '/' . $w . 'x' . $h . '_' . $filename, array('quality' => $this->quality));
        }
        return new Response($image->get("png"), 200, array(
                'Content-type' => mime_content_type(realpath($path) . '/' . $w . 'x' . $h . '_' . $filename)
            )
        );
    }
}