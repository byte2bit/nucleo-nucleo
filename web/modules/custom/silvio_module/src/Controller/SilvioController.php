<?php

namespace Drupal\silvio_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;

class SilvioController extends ControllerBase
{

    // class SilvioController {
    public function welcome()
    {
        return [
            '#markup' => 'Welcome to our Website.',
        ];

        $file = File::create([
            'uid' => 4657,
            'filename' => 'picture-2669-1626720887.jpg',
            'uri' => 'public://pictures/picture-2669-1626720887.jpg',
            'status' => 1,
        ]);
        $file->save();
    }
}
