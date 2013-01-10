<?php

class Shaw_View_Helper_MediaManager extends Zend_View_Helper_Abstract
{
    public function mediaManager($string)
    {
       Shaw_Log::debug('need ' . $string);
    }
}