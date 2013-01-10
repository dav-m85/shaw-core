<?php

class Shaw_View_Helper_Redirected extends Zend_View_Helper_Abstract
{
    public function redirected($alwaysShow = false, $defaultUrl = '/', $defaultTitle = '← Revenir en arrière')
    {
        // Perform redirect.
        $session = new Zend_Session_Namespace('Default');
        if( $session && ( ($redirectUrl = $session->redirectUrl) != null)){
            if($redirectTitle = $session->redirectTitle){
                $defaultTitle = $redirectTitle;
            }
            if($redirectUrl){
                if(stripos($redirectUrl, '?')){
                    $redirectUrl .= '&r=false';
                } else {
                    $redirectUrl .= '?r=false';
                }
                return sprintf('<a href="%s">%s</a>', $redirectUrl, $defaultTitle);
            }
        } else if($alwaysShow) {
            if(stripos($defaultUrl, '?')){
                $defaultUrl .= '&r=false';
            } else {
                $defaultUrl .= '?r=false';
            }
            return sprintf('<a href="%s">%s</a>', $defaultUrl, $defaultTitle);
        }
        return false;
    }
}