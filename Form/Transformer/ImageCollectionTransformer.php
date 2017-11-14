<?php

namespace Satoripop\ImagesBundle\Form\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Satoripop\ImagesBundle\Entity\Image;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageCollectionTransformer implements DataTransformerInterface
{

    /**
     * @var string $class
     */
    private $class;
    /**
     * @var ArrayCollection $data
     */
    private $data;

    public function __construct($class, $data)
    {
        $this->class = $class;
        $this->data = $data;
    }

    public function transform($values)
    {
        $this->data = $values;
        return $values;
    }

    public function reverseTransform($values)
    {
        $returns = array();
        if (is_array($values) || $values instanceof \Traversable) {
            foreach ($values as $value) {
                $entity = new $this->class();
                if ($value instanceof UploadedFile)
                    $entity->setFile($value);
                $returns[] = $entity;
            }
            return new ArrayCollection($returns);
        } else {
            return $values;
        }
    }

}