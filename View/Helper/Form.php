<?php

/** Zend_View_Helper_FormElement */
require_once 'Zend/View/Helper/FormElement.php';

/**
 * Helper for rendering HTML forms
 *
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_Form extends Zend_View_Helper_FormElement
{
    /**
     * Render HTML form
     *
     * @param  string $name Form name
     * @param  null|array $attribs HTML form attributes
     * @param  false|string $content Form content
     * @return string
     */
    public function form($name, $attribs = null, $content = false)
    {
        $info = $this->_getInfo($name, $content, $attribs);
        extract($info);

        if (!empty($id)) {
            $id = ' id="' . $this->view->escape($id) . '"';
        } else {
            $id = '';
        }

        if (array_key_exists('id', $attribs) && empty($attribs['id'])) {
            unset($attribs['id']);
        }
        
        if (!empty($name) && !($this->_isXhtml() && $this->_isStrictDoctype())) {
            $name = ' name="' . $this->view->escape($name) . '"';
        } else {
            $name = '';
        }
        
        if ( array_key_exists('name', $attribs) && empty($attribs['id'])) {
            unset($attribs['id']);
        }

        $xhtml = '<form'
               . $id
               . $name
               . $this->_htmlAttribs($attribs)
               . '>';

        if (false !== $content) {
            $xhtml .= $content;
            $xhtml .= '</form>';
        }
        
        return $xhtml;
    }
}
