<?php

class Shaw_Translate_Adapter_Void extends Zend_Translate_Adapter {
	/**
	 * Generates the adapter
	 */
	public function __construct($options = array()) {
	}
	/**
	 * Add translations
	 */
	public function addTranslation($options = array()) {
		return $this;
	}
	/**
	 * Sets new adapter options
	 */
	public function setOptions(array $options = array()) {
		return $this;
	}
	/**
	 * Returns the adapters name and it's options
	 */
	public function getOptions($optionKey = null) {
		return null;
	}
	/**
	 * Gets locale
	 */
	public function getLocale() {
		return null;
	}
	/**
	 * Sets locale
	 */
	public function setLocale($locale) {
		return $this;
	}
	/**
	 * Returns the available languages from this adapter
	 */
	public function getList() {
		return null;
	}
	/**
	 * Returns the message id for a given translation
	 */
	public function getMessageId($message, $locale = null) {
		return false;
	}
	/**
	 * Returns all available message ids from this adapter
	 */
	public function getMessageIds($locale = null) {
		return false;
	}
	/**
	 * Returns all available translations from this adapter
	 */
	public function getMessages($locale = null) {
		return array();
	}
	/**
	 * Is the wished language available ?
	 */
	public function isAvailable($locale) {
		return true;
	}
	/**
	 * Load translation data
	 */
	protected function _loadTranslationData($data, $locale, array $options = array()) {
		return array();
	}
	/**
	 * Translates the given string
	 */
	public function translate($messageId, $locale = null) {
		return $messageId;
	}
	/**
	 * Translates the given string using plural notations
	 */
	public function plural($singular, $plural, $number, $locale = null) {
		return $singular;
	}
	/**
	 * Logs a message when the log option is set
	 */
	protected function _log($message, $locale) {
	}
	/**
	 * Translates the given string
	 */
	public function _($messageId, $locale = null) {
		return $this->translate($messageId, $locale);
	}
	/**
	 * Checks if a string is translated within the source or not
	 */
	public function isTranslated($messageId, $original = false, $locale = null) {
		return true;
	}
	/**
	 * Returns the set cache
	 *
	 * @return Zend_Cache_Core The set cache
	 */
	public static function getCache() {
		return null;
	}
	/**
	 * Sets a cache for all Zend_Translate_Adapters
	 *
	 * @param Zend_Cache_Core $cache Cache to store to
	 */
	public static function setCache(Zend_Cache_Core $cache) {
	}
	/**
	 * Returns true when a cache is set
	 *
	 * @return boolean
	 */
	public static function hasCache() {
		return false;
	}
	/**
	 * Removes any set cache
	 *
	 * @return void
	 */
	public static function removeCache() {
	}
	/**
	 * Clears all set cache data
	 *
	 * @param string $tag Tag to clear when the default tag name is not used
	 * @return void
	 */
	public static function clearCache($tag = null) {
	}
	/**
	 * Returns the adapter name
	 *
	 * @return string
	 */
	public function toString() {
		return 'Void';
	}
}
