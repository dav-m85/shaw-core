<?php

/** Zend_Locale */
require_once 'Zend/Locale.php';

/** Zend_View_Helper_Abstract.php */
require_once 'Zend/View/Helper/Abstract.php';

/**
 * Translation view helper
 *
 * @category  Zend
 * @package   Zend_View
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Shaw_View_Helper_Tr extends Zend_View_Helper_Translate
{
	public function tr($messageid = null){
		return parent::translate($messageid);
	}
}