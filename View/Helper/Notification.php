<?php

class Shaw_View_Helper_Notification extends Zend_View_Helper_Abstract
{
    public function notification()
    {
        $flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        
        // Retrieves messages.
        $messages = array_merge($flash->getCurrentMessages(), $flash->getMessages());
        $flash->clearMessages(); $flash->clearCurrentMessages();
        
        
        foreach($messages as $message){
            if(! is_array($message)){
                $message = array('type' => 'info', 'message' => $message);
            }
            
            switch($message['type']){
                default :
                case 'success':
                    $successMessages[] =  $message['message'];
                    break;
                case 'info':
                    $infoMessages[] = $message['message'];
                    break;
                case 'warning':
                    $warningMessages[] = $message['message'];
                    break;
                case 'error':
                    $errorMessages[] = $message['message'];
                    break;
                case 'debug':
                    $debugMessages[] = $message['message'];
                    break;
            }
        }
        
        $markup = '';
        
        if(!empty($successMessages))
            $markup .= $this->view->htmlList($successMessages, false, array('class' => 'notification notification-success'), false);
            
        if(!empty($infoMessages))
            $markup .= $this->view->htmlList($infoMessages, false, array('class' => 'notification notification-info'), false);
            
        if(!empty($warningMessages))
            $markup .= $this->view->htmlList($warningMessages, false, array('class' => 'notification notification-warning'), false);
            
        if(!empty($errorMessages))
            $markup .= $this->view->htmlList($errorMessages, false, array('class' => 'notification notification-error'), false);
            
        if(!empty($debugMessages) && APPLICATION_ENV != 'production') // Never appears in prod.
            $markup .= $this->view->htmlList($debugMessages, false, array('class' => 'notification notification-developer'), false);
        
        return $markup;
    }
}