<?php

namespace Satoripop\ImagesBundle\Command;

use Satoripop\ImagesBundle\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

class AbsolutePathCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sp_image:regenerate:absolute_path')
            ->setDescription('Regenerate the absolute pathes for images');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getDoctrine()->getManagerForClass($this->getClass());
        $dialog = $this->getHelperSet()->get('dialog');
        try {
            $pictures = $em->getRepository("SynacomCentralBundle:Picture")->findAll();
        } catch (\Exception $e) {
            $output->writeln("<error>Exception: " . $e->getMessage() . "</error>");
            $output->writeln("in " . $e->getFile() . " line " . $e->getLine());
            foreach ($e->getTrace() as $debug) {
                $output->writeln("<info>in " . $debug['file'] . " line " . $debug['line'] . "</info>");
                if (!$dialog->askConfirmation($output, 'Stop debugging ? (Press Enter to continue)', true)) {
                    return;
                }
            }
            return;
        }
        $uploadPath = $this->getUploadPath();
        $processed = 0;
        $errors = 0;
        foreach ($pictures as $picture) {
            if ($picture instanceof Image) {
                $output->writeln("Processing " . $picture->getPath());
                if ($absolutePath = realpath($uploadPath . '/' . $picture->getPath())) {
                    $picture->setAbsolutePath($absolutePath);
                    $em->flush($picture);
                    $processed++;
                    $output->writeln("Done processing " . $picture->getPath());
                } else {
                    $errors++;
                    $output->writeln($uploadPath . '/' . $picture->getPath() . " is not a correct path. Please verify your configuration.");
                    if (!$dialog->askConfirmation($output, 'Do you want to continue ? (Press Enter to continue)', true)) {
                        break;
                    }
                }
            }
        }

        $output->writeln("Done processing $processed absolute paths with $errors errors.");
    }

    /**
     * Shortcut to return the image upload path.
     *
     * @return string
     *
     * @throws \LogicException If upload path is not in configuration
     */
    public function getUploadPath()
    {
        if (!$this->getContainer()->hasParameter('sp_images.upload_path')) {
            throw new \LogicException('The upload path is not configured in your application.');
        }

        return $this->getContainer()->getParameter('sp_images.upload_path');
    }

    /**
     * Shortcut to return the image class.
     *
     * @return string
     *
     * @throws \LogicException If Image class is not in configuration
     */
    public function getClass()
    {
        if (!$this->getContainer()->hasParameter('sp_images.image_class')) {
            throw new \LogicException('The image class is not configured in your application.');
        }

        return $this->getContainer()->getParameter('sp_images.image_class');
    }

    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return Registry
     *
     * @throws \LogicException If DoctrineBundle is not available
     */
    public function getDoctrine()
    {
        if (!$this->getContainer()->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application.');
        }

        return $this->getContainer()->get('doctrine');
    }

}