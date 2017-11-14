<?php
namespace Satoripop\ImagesBundle\Form\Transformer;

use Doctrine\ORM\EntityManager;
use Satoripop\ImagesBundle\Entity\Image;
use Symfony\Component\Form\DataTransformerInterface;

class ImageTransformer implements DataTransformerInterface
{

    /**
     * @var EntityManager $em
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function transform($image)
    {
        return $image;
    }

    public function reverseTransform($image)
    {
        if ($image instanceof Image && !empty($data = $image->getFileData())) {
            if (isset($data->remove) && $data->remove) {
                $this->em->remove($image);
                return null;
            }
        }
        return $image;
    }

}