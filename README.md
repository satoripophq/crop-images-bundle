Getting Started With SatoripopImagesBundle
===================================

This simplistic bundles provides a wrapping layer for the Imagine Class and implements simple tools for image uploads and thumbnail generation.

##### Quick access: [Prerequisites](https://github.com/Satoripop/SatoripopImagesBundle#prerequisites) - [Installation](https://github.com/Satoripop/SatoripopImagesBundle#installation)

## Prerequisites

This version of the bundle requires Symfony 2.1+. If you are using Symfony
2.0.x, please use the 1.2.x releases of the bundle.

### Translations

If you wish to use default texts provided in this bundle, you have to make
sure you have translator enabled in your config.

``` yaml
# app/config/config.yml

framework:
    translator: ~
```

For more information about translations, check [Symfony documentation](http://symfony.com/doc/current/book/translation.html).

## Installation

### Step 1: Download SatoripopImagesBundle using composer

Add SatoripopImagesBundle in your composer.json:

```js
{
    "require": {
        "Satoripophq/crop-images-bundle": "dev-master"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update Satoripophq/crop-images-bundle
```

Composer will install the bundle to your project's `vendor/Satoripop` directory.

### Step 2: Create your Image class

The goal of this bundle is to persist some `Image` class to a MySql database.
Your first job, then, is to create the `Image` class
for your application. This class can look and act however you want: add any
properties or methods you find useful. This is *your* `Image` class.

The bundle provides base classes which are already mapped for most fields
to make it easier to create your entity. Here is an example on how you use it:

**Warning:**

> When you extend from the mapped superclass provided by the bundle, don't
> redefine the mapping for the other fields as it is provided by the bundle.

Your `Image` class can live inside any bundle in your application. For example,
if you work at "Acme" company, then you might create a bundle called `AcmeImagesBundle`
and place your `Image` class in it.

``` php
<?php

namespace Acme\ImageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Satoripop\ImagesBundle\Entity\Image as BaseImage;

/**
 * Image
 *
 * @ORM\Table(name="image")
 * @ORM\Entity
 */
class Image extends BaseImage
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;


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
}
```

### Step 3: Configure the SatoripopImagesBundle

Now that you have included  the SatoripopImagesBundle into your project, the next step is to configure the bundle to work with
the specific needs of your application.

Add the following configuration to your `config.yml` file according to which type
of datastore you are using.

``` yaml
# app/config/config.yml
sp_images:
    image_class: Acme\ImagesBundle\Entity\Image
    write_to: %kernel.root_dir%/../public_html
    generate_thumbs: true
    thumbs: ['200x200',[350,350],{'w':128,'h':128}]
```

Only one configuration value is required to use the bundle:

* The fully qualified class name (FQCN) of the `Image` class which you created in Step 2.

### Step 4: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Satoripop\ImagesBundle\SatoripopImagesBundle(),
    );
}
```

### Step 5: Import SatoripopImagesBundle routing files

Now that you have activated and configured the bundle, all that is left to do is
import the SatoripopImagesBundle routing files.

By importing the routing files you will have ready made pages for managing images and creating thumbnails.

*Note:**

> Only the thumbnails routing is required

In YAML:

``` yaml
# app/config/routing.yml
#on-the-fly thumbnail generation (required)
sp_images_routes:
    resource: .
    type: sp_images

#crud routing (optional)
sp_images_crud:
    resource: "@SatoripopImagesBundle/Resources/config/routing/image.xml"
    prefix: /images

```

### Step 6: Update your database schema

Now that the bundle is configured, the last thing you need to do is update your
database schema because you have added a new entity, the `Image` class which you
created in Step 2.

For ORM run the following command.

``` bash
$ php app/console doctrine:schema:update --force
```
