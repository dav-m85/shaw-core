<?php

class Shaw_View_Helper_Image 
extends Zend_View_Helper_Abstract
{
    public function image($width = 100, $height = 100)
    {
        return sprintf('<img src="/image.php?w=%s&h=%s" />', $width, $height);
    }
}