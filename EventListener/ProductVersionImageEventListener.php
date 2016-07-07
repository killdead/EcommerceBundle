<?php

namespace Ziiweb\EcommerceBundle\EventListener;


use Doctrine\ORM\Event\LifecycleEventArgs;
use Ziiweb\EcommerceBundle\Entity\ProductVersionImage;

class ProductVersionImageEventListener
{

    public function prePersist(LifecycleEventArgs $args) 
    {
        $entity = $args->getEntity();


        if ($entity instanceof ProductVersionImage) {

            $file = $entity->getFile();

            $filename = md5(uniqid()) . '.' . $file->guessExtension(); 


            $sizes = array('s' => 100, 'm' => 200, 'l' => 400, 'xl' => 600);

            foreach ($sizes as $key => $size) {
                ${'imagick' . $key} = new \Imagick($file->getRealPath()); 
		$newWidth = $size;
                $newHeight = ${'imagick' . $key}->getImageHeight() * $newWidth / ${'imagick' . $key}->getImageWidth(); 
		${'imagick' . $key}->resizeImage($newWidth, $newHeight, false, 1);  
		//${'imagick' . $key}->setImageCompression(\Imagick::COMPRESSION_JPEG); 
		//${'imagick' . $key}->setImageCompressionQuality(85);
		${'imagick' . $key}->writeImage('uploads/' . $key . '/' . $filename);
            }
            
         
            $file->move('uploads', $filename);
            

            $entity->setFile($filename);
        }
    }

}
