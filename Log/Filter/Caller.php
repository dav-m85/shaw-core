<?php

/** Zend_Log_Filter_Abstract */
require_once 'Zend/Log/Filter/Abstract.php';

class Shaw_Log_Filter_Caller 
extends Zend_Log_Filter_Abstract
{
    /**
     * @var integer
     */
    protected $_callers;

    /**
     * @return void
     * @throws Zend_Log_Exception
     */
    public function __construct($callers, $operator = null)
    {
        $this->_callers = $callers;
    }

    /**
     * Create a new instance of Zend_Log_Filter_Priority
     *
     * @param  array|Zend_Config $config
     * @return Zend_Log_Filter_Priority
     */
    static public function factory($config)
    {
        $config = self::_parseConfig($config);
        $config = array_merge(array(
            'callers' => null,
        ), $config);

        $config['callers'] = explode(',', $config['callers']);
        
        return new self(
            $config['callers']
        );
    }

    /**
     * Returns TRUE to accept the message, FALSE to block it.
     *
     * @param  array    $event    event data
     * @return boolean            accepted?
     */
    public function accept($event)
    {
        return ! in_array($event['caller'], $this->_callers);
    }
}
