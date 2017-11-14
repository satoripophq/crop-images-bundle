<?php
namespace Satoripop\CropImagesBundle\Listener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Proxy\Exception;
use Doctrine\Common\EventSubscriber;
use Satoripop\CropImagesBundle\Entity\Image;
use Satoripop\CropImagesBundle\Util\Slugger;
use Imagine\Gd\Imagine;
use Imagine\Filter\Transformation;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Satoripop\CropImagesBundle\Services\PathMaker;
use Imagine\Image\Point;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 *
 * @author Mohamed Racem Zouaghi <racem.zouaghi@satoripop.tn>
 *
 */
class ImageListener implements EventSubscriber
{

    /**
     * @var string $upload_path
     */
    private $upload_path;

    /**
     * @var string $cache_path
     */
    private $cache_path;

    /**
     * @var boolean $generate_thumbs
     */
    private $generate_thumbs;

    /**
     * @var array $thumb_sizes
     */
    private $thumb_sizes;

    /**
     * @var integer $thumb_quality
     */
    private $thumb_quality;

    /**
     * @var PathMaker $pm
     */
    private $pm;

    /**
     * @var Slugger $slugger
     */

    private $slugger;

    /**
     * @var string $phantom_folder
     */
    private $phantom_folder;

    /**
     * @param string $upload_path
     * @param string $cache_path
     * @param boolean $generate_thumbs
     * @param array $thumb_sizes
     * @param integer $thumb_quality
     * @param PathMaker $pm
     * @param string $phantom_folder
     */
    public function __construct($upload_path, $cache_path, $generate_thumbs, $thumb_sizes, $thumb_quality, PathMaker $pm, $phantom_folder)
    {
        $this->upload_path = $upload_path;
        $this->cache_path = $cache_path;
        $this->generate_thumbs = $generate_thumbs;
        $this->thumb_quality = $thumb_quality;
        $this->thumb_sizes = $thumb_sizes;
        $this->pm = $pm;
        $this->slugger = new Slugger();
        $this->phantom_folder = $phantom_folder;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
            'preRemove'
        );
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Image) {
            $this->processImageUpload($entity);
            $entity->setUploadedAt(new \DateTime());
            $entity->setUpdatedAt(new \DateTime());
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {

        $entity = $args->getEntity();
        if ($entity instanceof Image) {
            $em = $args->getEntityManager();
            $this->processImageUpload($entity, $em);
            $entity->setUpdatedAt(new \DateTime());
            $uow = $em->getUnitOfWork();
            $meta = $em->getClassMetadata(get_class($entity));
            $uow->recomputeSingleEntityChangeSet($meta, $entity);
            $entity->addMetaData($entity->getUpdatedAt()->getTimestamp(),"UploadedAt");
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Image) {
            $this->processImageDelete($entity);
        }
    }

    /**
     * @param Image $image
     * @param EntityManager|null $em
     */
    private function processImageUpload(Image &$image, EntityManager $em = null)
    {
        $this->cleanPhantomFolder();
        if (null === $image->getPhantomPath() && null === $image->getFile() && null === $image->getUrl() && !count($image->getMetaData())) {
            return;
        }
        $absolute_path = $this->upload_path . '/';
        if ($image->getPath() && ($image->getFile() || $image->getUrl())) {
            $this->processImageDelete($image);
            $this->initImageConfig($image);
        }
        if ($image->getFile() || $image->getPhantomPath()) {
            if ($image->getPhantomPath()) {
                $image->setPhantomPath($this->phantom_folder . $image->getPhantomPath());
            }
            dump($image->getMetaData());
            $image->setPath($this->generateImagePath($image->getMetaData(), $image->getPhantomPath() ? pathinfo($image->getPhantomPath(), PATHINFO_EXTENSION) : $image->getFile()->guessExtension()));
            $image->setAbsolutePath($image->getPhantomPath() ? $image->getPhantomPath() : $this->upload_path . '/' . $image->getPath());
            $image->setMimeType($image->getPhantomPath() ? mime_content_type($image->getPhantomPath()) : $image->getFile()->getClientMimeType());
            if ($data = $image->getFileData()) {
                $filename = $image->getPhantomPath() ? $image->getPhantomPath() : $image->getFile()->getPath() . '/' . $image->getFile()->getFilename();
                $imagine = new Imagine();
                $file = $imagine->open($filename);
                $file->rotate($data->rotate)
                    ->crop(new Point(round($data->x), round($data->y)), new Box($data->width, $data->height))
                    ->save($this->upload_path . '/' . $image->getPath());
                $image->unsetFileData();
            } else {
                if ($image->getPhantomPath()) {
                    rename($image->getPhantomPath(), $this->upload_path . '/' . $image->getPath());
                } elseif ($image->getFile()) {
                    $image->getFile()->move($this->upload_path, $image->getPath());
                }
            }
            $image->unsetFile();
            $image->unsetPhantomPath();
        } elseif ($image->getUrl()) {
            $headers = @get_headers($image->getUrl());
            $extension = null;
            if ($headers):
                foreach ($headers as $header):
                    if (str_replace('Content-Type: ', '', $header) != $header):
                        $extension = substr(str_replace('Content-Type: ', '', $header), strrpos(str_replace('Content-Type: ', '', $header), '/') + 1);
                        $image->setMimeType(str_replace('Content-Type: ', '', $header));
                        break;
                    endif;
                endforeach;
            endif;
            $image->setPath($this->generateImagePath($image->getMetaData(), $extension));
            $image->setAbsolutePath($absolute_path . '/' . $image->getPath());
            $content = file_get_contents($image->getUrl());
            file_put_contents($absolute_path . '/' . $image->getPath(), $content, LOCK_EX);
            $image->unsetUrl();
        }
        if (count($meta_data = $image->getMetaData())) {
            if ($filename = realpath($this->upload_path . '/' . $image->getPath())) {
                $this->cacheClear($image);
                $dir = dirname($filename);
                $new_name = $this->generateImagePath($meta_data, pathinfo($filename, PATHINFO_EXTENSION));
                if ($new_name != $image->getPath()) {
                    rename($filename, "$dir/" . $new_name);
                    $image->setPath($new_name);
                    $image->setAbsolutePath(realpath($this->upload_path . '/' . $image->getPath()));
                }
            }
        }
        try {
            $this->processFileData($image, $absolute_path);
        } catch (\Exception $e) {

        }

    }

    private function cleanPhantomFolder()
    {
        $pw = new PathMaker();
        if (($handle = @opendir($this->phantom_folder)) || ($pw->makePath($this->phantom_folder) && $handle = @opendir($this->phantom_folder))) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && realpath($this->phantom_folder . '/' . $entry) && filemtime($this->phantom_folder . '/' . $entry) <= (time() - 3600 * 2)) {
                    unlink(realpath($this->phantom_folder . '/' . $entry));
                }
            }
            closedir($handle);
        }
    }

    /**
     * @param array $meta_data
     * @param string $extension
     * @return string
     */
    private function generateImagePath($meta_data = array(), $extension)
    {
        $absolute_path = $this->upload_path . '/';
        if (count($meta_data)) {
            $base_name = implode(' ', $meta_data);
        } else {
            $base_name = sha1(uniqid(mt_rand(), true));
        }
        $i = 0;
        $filename = substr($this->slugger->urlize($base_name), -120);
        while (realpath("$absolute_path.$filename.$extension")) {
            $filename = substr($this->slugger->urlize("$base_name $i"), -120);
        }
        return "$filename.$extension";
    }

    /**
     * @param Image $image
     */
    private function processImageDelete(Image $image)
    {
        $absolute_path = realpath($this->upload_path . '/' . $image->getPath());
        if (file_exists($absolute_path)) {
            unlink($absolute_path);
        }
        $this->cacheClear($image);
    }

    /**
     * @param Image $image
     */
    private function cacheClear(Image $image)
    {
        $path = $image->getPath();
        if ($handle = @opendir($this->cache_path)) {
            while (false !== ($entry = readdir($handle))) {
                if (is_dir($this->cache_path . '/' . $entry) && is_file($this->cache_path . '/' . $entry . '/' . $path)) {
                    unlink($this->cache_path . '/' . $entry . '/' . $path);
                }
            }
            closedir($handle);
        }
    }

    /**
     * @param Image $image
     */
    private function initImageConfig(Image &$image)
    {
        if ($file = is_file(realpath($this->upload_path . '/' . $image->getPath()))) {
            unlink($file);
        }
        if ($image->getUploadedAt() == null):
            $image->setUploadedAt(new \DateTime());
        endif;
        $image->setUpdatedAt(new \DateTime());
    }

    /**
     * @param Image $file
     * @param string $absolute_path
     */
    private function processFileData(Image &$file, $absolute_path)
    {
        $sizes = getimagesize($absolute_path . '/' . $file->getPath());
        $file->setWidth($sizes[0]);
        $file->setHeight($sizes[1]);
        $file->setFileSize(filesize($absolute_path . '/' . $file->getPath()));
        if ($this->generate_thumbs) {
            $this->generateThumbs($file);
        }
    }

    /**
     * @param Image $image
     */
    private function generateThumbs(Image $image)
    {
        $filename = realpath($this->upload_path . '/' . $image->getPath());
        $imagine = new Imagine();
        foreach ($this->thumb_sizes as $size) {
            $transformation = new Transformation();
            $file = $imagine->open($filename);
            if (!is_array($size)) {
                $size = explode('x', $size);
            }
            $transformation->thumbnail(new Box(isset($size['w']) ? $size['w'] : reset($size), isset($size['h']) ? $size['h'] : end($size)), ImageInterface::THUMBNAIL_OUTBOUND);
            if ($this->pm->makePath($this->cache_path . '/' . reset($size) . 'x' . end($size))) {
                $transformation->apply($file)->save(realpath($this->cache_path . '/' . (isset($size['w']) ? $size['w'] : reset($size)) . 'x' . (isset($size['h']) ? $size['h'] : end($size))) . '/' . $image->getPath(), array('quality' => $this->thumb_quality));
            }
        }
    }
}