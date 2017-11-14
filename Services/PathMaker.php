<?php

namespace Satoripop\CropImagesBundle\Services;

/**
 *
 * @author Mohamed Racem Zouaghi <racem.zouaghi@satoripop.tn>
 *
 */
class PathMaker
{
    public function makePath($pathname)
    {
        // Check if directory already exists
        if (is_dir($pathname) || empty($pathname)) {
            return true;
        }
        // Ensure a file does not already exist with the same name
        $pathname = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $pathname);
        if (is_file($pathname)) {
            trigger_error('mkdirr() File exists', E_USER_WARNING);
            return false;
        }
        // Crawl up the directory tree
        $next_pathname = substr($pathname, 0, strrpos($pathname, DIRECTORY_SEPARATOR));
        if ($this->makePath($next_pathname)) {
            if (!file_exists($pathname)) {
                return mkdir($pathname);
            }
        }
        return false;
    }
} 