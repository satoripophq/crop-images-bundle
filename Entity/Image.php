<?php

namespace Satoripop\CropImagesBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Satoripop\CropImagesBundle\Validator\Constraints as CustomAssert;

/**
 * Image
 *
 * @author Mohamed Racem Zouaghi <racem.zouaghi@satoripop.tn>
 */
abstract class Image
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $absolutePath;

    /**
     * @var string
     */
    private $mimeType;

    /**
     * @var \DateTime
     */
    private $uploadedAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $fileSize;

    /**
     * @var integer
     */
    private $width;

    /**
     * @var integer
     */
    private $height;

    /**
     * @var array
     */
    private $xiff;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string $phantomPath
     */
    private $phantomPath;

    /**
     * @var UploadedFile $file
     *
     * @Assert\Image()
     */
    private $file;

    /**
     * @var json
     */
    private $file_data;

    /**
     * @var array
     */
    private $meta_data;

    /**
     * @Assert\Url()
     * @var string $url
     */
    private $url;

    /**
     * Set path
     *
     * @param string $path
     * @return Image
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set absolutePath
     *
     * @param string $absolutePath
     * @return Image
     */
    public function setAbsolutePath($absolutePath)
    {
        $this->absolutePath = $absolutePath;

        return $this;
    }

    /**
     * Get absolutePath
     *
     * @return string
     */
    public function getAbsolutePath()
    {
        return $this->absolutePath;
    }

    /**
     * Set mimeType
     *
     * @param string $mimeType
     * @return Image
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get mimeType
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set uploadedAt
     *
     * @param \DateTime $uploadedAt
     * @return Image
     */
    public function setUploadedAt($uploadedAt)
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    /**
     * Get uploadedAt
     *
     * @return \DateTime
     */
    public function getUploadedAt()
    {
        return $this->uploadedAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Image
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Image
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Image
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set fileSize
     *
     * @param string $fileSize
     * @return Image
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * Get fileSize
     *
     * @return string
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * Set width
     *
     * @param integer $width
     * @return Image
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     * @return Image
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set xiff
     *
     * @param string $xiff
     * @return Image
     */
    public function setXiff($xiff)
    {
        $this->xiff = $xiff;

        return $this;
    }

    /**
     * Get xiff
     *
     * @return string
     */
    public function getXiff()
    {
        return $this->xiff;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set file
     *
     * @param UploadedFile $file
     * @return Picture
     */
    public function setFile($file, $data = null)
    {
        $this->file = $file;
        $this->file_data = $data;
        $this->fileSize = null;
        return $this;
    }

    /**
     * Set file
     *
     * @return Picture
     */
    public function unsetFile()
    {
        $this->file = null;
        return $this;
    }

    /**
     * Set file
     *
     * @return Picture
     */
    public function unsetPhantomPath()
    {
        $this->phantomPath = null;
        return $this;
    }

    /**
     * Get file
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set file
     *
     * @param json $data
     * @return Picture
     */
    public function setFileData($data)
    {
        $this->file_data = is_object($data) ? $data : json_decode($data);
        $this->updatedAt = new \DateTime();
        return $this;
    }

    /**
     * Set file
     *
     * @return Picture
     */
    public function unsetFileData()
    {
        $this->file_data = null;
        return $this;
    }

    /**
     * Get file
     *
     * @return json
     */
    public function getFileData()
    {
        return $this->file_data;
    }

    /**
     * Set meta data
     *
     * @param array $data
     * @return Picture
     */
    public function setMetaData($data)
    {
        $this->meta_data = is_array($data) ? $data : array($data);
        return $this;
    }

    /**
     * Set meta data
     *
     * @param array $data
     * @return Picture
     */
    public function addMetaData($data, $key)
    {
        if (!$this->meta_data) {
            $this->meta_data = array();
        }
        $this->meta_data[$key] = $data;
        return $this;
    }

    /**
     * Set meta data
     *
     * @return Picture
     */
    public function unsetMetaData()
    {
        $this->meta_data = null;
        return $this;
    }

    /**
     * Get meta data
     *
     * @return array
     */
    public function getMetaData()
    {
        if ($this->meta_data)
            ksort($this->meta_data);
        return $this->meta_data;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Picture
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Picture
     */
    public function unsetUrl()
    {
        $this->url = null;
        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function __toString()
    {
        return $this->path ? $this->path : '';
    }

    /**
     * @param string $phantomPath
     * @return Image
     */
    public function setPhantomPath($phantomPath)
    {
        $this->phantomPath = $phantomPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhantomPath()
    {
        return $this->phantomPath;
    }
}