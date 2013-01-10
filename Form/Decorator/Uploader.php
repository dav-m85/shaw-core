<?php

class Shaw_Form_Decorator_Uploader
extends Zend_Form_Decorator_Abstract
implements Zend_Form_Decorator_Marker_File_Interface
{
    /**
     * Attributes that should not be passed to helper
     * @var array
     */
    protected $_attribBlacklist = array('helper', 'placement', 'separator', 'value');

    /**
     * Default placement: append
     * @var string
     */
    protected $_placement = 'APPEND';

    /**
     * Get attributes to pass to file helper
     *
     * @return array
     */
    public function getAttribs()
    {
        $attribs   = $this->getOptions();

        if (null !== ($element = $this->getElement())) {
            $attribs = array_merge($attribs, $element->getAttribs());
        }

        foreach ($this->_attribBlacklist as $key) {
            if (array_key_exists($key, $attribs)) {
                unset($attribs[$key]);
            }
        }

        return $attribs;
    }

    /**
     * Render a form file
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        
        if (!$element instanceof Zend_Form_Element) {
            return $content;
        }

        $view = $element->getView();
        if (!$view instanceof Zend_View_Interface) {
            return $content;
        }

        $name      = $element->getName();
        $attribs   = $this->getAttribs();
        if (!array_key_exists('id', $attribs)) {
            $attribs['id'] = $name;
        }

        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $markup = array();
        $value = $element->getValue();
        
        $style = '';
        if($value){
        	$style = 'style="background-image:url('.$value.')"';
        }
        
        $markup[] = '<button class="btn btn-mini pull-left" style="margin-right: 10px;" onclick="$(\'#qq-uploader-modal\').data(\'target\', this).modal(\'show\');" type="button">Upload</button>';
       // $markup[] = '<div class="uploader-image pull-left"  '.$style.'></div>';
        if($value){
        	$markup[] = '<img src="'.$value.'" class="pull-left uploadimg" style="border:1px dashed #AAA;" />';
        }
        $markup[] = $view->formHidden($element->getName(), $value);        
    	$markup = join($separator, $markup);
    	
    	$this->_addJavascript();
    	
        switch ($placement) {
            case self::PREPEND:
                return $markup . $separator . $content;
            case self::APPEND:
            default:
                return $content . $separator . $markup;
        }
    }
    
    protected function _addJavascript()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$view = $layout->getView();
    	
    	$uploadUrl = $view->url('index', 'upload');
    	
$script = <<<EOF
$(function(){
	// all the CSS lies inside fileuploader.css... that should be fixed.
	if(typeof qq == 'undefined'){
		console.log('qqUploader javascript library is missing !');
		return;
	}
	if(typeof Modal == 'undefined'){ // todo this test is not ok
		//console.log('Bootstrap Modal javascript library is missing !');
		//return;
	}
	
	var modalHtml = '\
<!-- Modal for Upload dialog -->\
<div class="modal hide" id="qq-uploader-modal">\
  <div class="modal-header">\
    <button type="button" class="close" data-dismiss="modal" >&times;</button>\
    <h3>Image</h3>\
    <p>Please specify an image to use</p>\
  </div>\
  <div class="modal-body">\
    <!-- Upload file OR Select URL -->\
	<div class="dropzone"></div>\
	<div class="divider"><span>OR</span></div>\
	<div class="form-inline">\
	<label>Image Url :</label>\
	<input class="qq-uploader-modal-url" type="text" class="span12" />\
	</div>\
  </div>\
  <div class="modal-footer">\
    <a class="btn" data-dismiss="modal">Cancel</a>\
    <a class="btn btn-primary submit">Save</a>\
  </div>\
</div>\
	';

	// Insert modal code if not already defined
	var modal = $('#qq-uploader-modal');

	if(modal.length == 0){
		console.log('inserting modal');
		$('body').append(modalHtml);
		modal = $('#qq-uploader-modal');
	}

	// Save event
	modal.bind('save', function(){
		var target = $(modal.data("target"));
		var url = modal.data("url");
		target.siblings('input').val(url);
		//target.next('div.uploader-image').css('background-image', 'url("' + url + '")');		
		$('.uploadimg').attr("src",url);
		modal.modal('hide');
	});

	// Submit link
	modal.find('a.submit').click(function(){
		var field = modal.find('input.qq-uploader-modal-url').val();
		$.post('$uploadUrl', {url: field}, function(responseJSON){
				modal.data("url", responseJSON.url);
	            modal.trigger('save');
			}, "json");
	});

	// Qq upload instance
	var uploader = new qq.FileUploader({
        element: modal.find('.dropzone').get(0),
        action: '$uploadUrl',
        debug: true,
        onComplete: function(id, fileName, responseJSON){
            modal.data("url", responseJSON.url);
            modal.trigger('save');
        }
    }); 
});
EOF;

    	$view->headScript()
		->appendScript($script);
    }
}
