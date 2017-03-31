<?php
/**
 * ImageExtension.php
 *
 * @author Bram de Leeuw
 * Date: 27/03/17
 */

namespace Broarm\EventTickets;

use DataExtension;
use Director;
use Image;

/**
 * Class ImageExtension
 * 
 * @property ImageExtension|Image $owner
 */
class ImageExtension extends DataExtension
{
    /**
     * Get a base 64 encoded variant of the image
     *
     * @return string
     */
    public function getBase64()
    {
        if ($this->owner->exists()) {
            $file = $this->owner->getFullPath();
            $mime = mime_content_type($file);
            $fileContent = file_get_contents($file);
            return "data://$mime;base64," . base64_encode($fileContent);
        }
        
        return null;
    }
}
