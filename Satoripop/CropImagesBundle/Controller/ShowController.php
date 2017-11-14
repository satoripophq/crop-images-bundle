<?php

namespace Satoripop\CropImagesBundle\Controller;

use Imagine\Image\Point;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * This controller creates a thumbnail and returns its content in case apache couldn't load it from the web folder.
 *
 * @author Mohamed Racem Zouaghi <racem.zouaghi@satoripop.tn>
 */
class ShowController extends Controller
{
    public function phantomAction($w, $h, $image, $mode)
    {
        $dir = $this->getParameter("kernel.cache_dir") . "/temp_buffer";
        return $this->get('sp_images.image_processor')->getThumb($w, $h, $dir . "/" . $image, $mode, false, true);
    }

    public function thumbAction($w, $h, $image, $mode, $filter)
    {
        return $this->get('sp_images.image_processor')->getThumb($w, $h, $image, $mode, true, false, $filter);
    }

    public function fullAction($image)
    {
        return $this->get('sp_images.image_processor')->getImage($image);
    }

    public function imagesNotFoundIconAction($w, $h, $icon = null)
    {
        return $this->get('sp_images.image_processor')->getNotFoundIcon($w, $h, str_replace('__', '/', $icon));

    }

    public function imagesNotFoundDefaultAction($w, $h, $default)
    {
        return $this->get('sp_images.image_processor')->getNotFoundDefault($w, $h, str_replace('__', '/', $default));
    }

    public function imagesNotFoundTextAction($w, $h, $text)
    {
        return $this->get('sp_images.image_processor')->getNotFoundText($w, $h, ucwords(str_replace('__', ' ', $text)));
    }
}
