<?php

include(dirname(__FILE__) . '/class.JavaScriptPacker.php');

class Shaw_View_Helper_JsObfuscator_Container
extends Zend_View_Helper_Placeholder_Container
{
	public function toString($indent = null)
	{
		$raw = parent::toString($indent);
		
		// Adapte
		/*
		$raw = str_replace("\\\r\n", "\\n", $raw);
		$raw = str_replace("\\\n", "\\n", $raw);
		$raw = str_replace("\\\r", "\\n", $raw);
		$raw = str_replace("}\r\n", "};\r\n", $raw);
		$raw = str_replace("}\n", "};\n", $raw);
		$raw = str_replace("}\r", "};\r", $raw);
		*/
		$gotTags = false;
		if(preg_match('!^\s*<script!', $raw)){
			$a = stripos($raw, '>');
			$b = strripos($raw, '</');
			if($a !== false  && $b !== false){
				$gotTags = true;
				$raw = substr($raw, $a + 1, $b - $a - 3);
			}
		}
		
		// @todo : remove after and following tag <script> if necessary
		
		$packer = new JavaScriptPacker($raw, 'Normal', true, false);
		$obfuscated = $packer->pack();
		
		if($gotTags){
			$obfuscated = '<script>' . $obfuscated . '</script>';
		}

		return $obfuscated;
	}
}