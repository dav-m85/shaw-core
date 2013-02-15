<?php
/**
 * Store the buckets on the filesystem.
 * 
 * Can be used as a Shaw_Chain link as well as independant storage. Works like a cache,
 * however does not support lifetyme for its stored values.
 * 
 * @author David Moreau <git@alfti.me>
 */
class Shaw_Chain_Store
extends Shaw_Chain_AbstractLink
{
	protected $_options = array(
	    /* where cache files are going */
        'path' => null,
	    /* shall hashing happen ? */
        'hashed_directory_level' => 0,
        'hashed_directory_umask' => 0700,
        'cache_file_umask' => 0600,
	    /* prefixing */
		'file_name_prefix' => '', // shall be shaw...
    );
	
	public function __construct($options = array())
	{
	    if(isset($options['path'])){
            $options['path'] = realpath($options['path']) . '/';
	        if(! file_exists($options['path'])){
	            throw new Exception('Store path does not exists. ' . $options['path']);
	        }
	        if(! is_writable($options['path'])){
	            throw new Exception('Store path is not writable. ' . $options['path']);
	        }
	    }
	    
	    $this->_options = array_merge( $this->_options, $options);
	}
	
	public function getStorePath()
	{
		return $this->_options['path'];
	}
	
	// Source data as long as its in cache
	function chainSource()
	{
		do{
			$req = $this->source();
			$hash = md5($req->url);
			
			if ( $this->test($hash) ){
				$req->output = $this->load($hash);
				
				$this->sink($req);
			}
			else{
				break;
			}
		}
		while($req);

		return $req;
	}
	
	function chainSink($req)
	{
		if($req->output != null){
			$hash = md5($req->url);
			$this->save($hash, $req->output);
		}
		$this->sink($req);
	}
	
	function test($id)
	{
		$fil = $this->_file($id);
		return file_exists($fil);
	}
	
	function remove($id)
	{
		$fil = $this->_file($id);
		if(file_exists($fil))
			return unlink($fil);
		return null;
	}
	
	function load($id)
	{
		Shaw_Log::debug('load from Store %s', $id);
        $file = $this->_file($id);
        $data = $this->_fileGetContents($file);
        return $data;
	}
	
	function file($id)
	{
		$file = $this->_file($id);
        $path = $this->_path($id);
        if ($this->_options['hashed_directory_level'] > 0) {
            if (!is_writable($path)) {
                // maybe, we just have to build the directory structure
                $this->_recursiveMkdirAndChmod($id);
            }
            if (!is_writable($path)) {
                return false;
            }
        }
        
        return $file;
	}
	
	function save($id, $data)
	{
		Shaw_Log::debug('save to Store %s', $id);
        $file = $this->_file($id);
        $path = $this->_path($id);
        if ($this->_options['hashed_directory_level'] > 0) {
            if (!is_writable($path)) {
                // maybe, we just have to build the directory structure
                $this->_recursiveMkdirAndChmod($id);
            }
            if (!is_writable($path)) {
                return false;
            }
        }
        
        $res = $this->_filePutContents($file, $data);
        return $res;
	}
	
	/**
     * Make and return a file name (with path)
     *
     * @param  string $id Cache id
     * @return string File name (with path)
     */
    protected function _file($id)
    {
        $path = $this->_path($id);
        $fileName = $this->_options['file_name_prefix'] . $id;
        return $path . $fileName;
    }
    
    /**
     * Return the complete directory path of a filename (including hashedDirectoryStructure)
     *
     * @param  string $id Cache id
     * @param  boolean $parts if true, returns array of directory parts instead of single string
     * @return string Complete directory path
     */
    protected function _path($id, $parts = false)
    {
        $partsArray = array();
        $root = $this->_options['path'];
        $prefix = $this->_options['file_name_prefix'];
        if ($this->_options['hashed_directory_level']>0) {
            $hash = $this->_hash_adler32_wrapper($id);
            for ($i=0 ; $i < $this->_options['hashed_directory_level'] ; $i++) {
                $root = $root . $prefix . substr($hash, 0, $i + 1) . DIRECTORY_SEPARATOR;
                $partsArray[] = $root;
            }
        }
        if ($parts) {
            return $partsArray;
        } else {
            return $root;
        }
    }
	
    // @see http://stackoverflow.com/questions/1316881/php-how-to-calculate-adler32-checksum-for-zip
    // Reversed adler function with php5.2.11 bug in mind
    private function _hash_adler32_wrapper($data) {
    	$digHexStr = hash("adler32", $data);
    
    	// If version is better than 5.2.11 no further action necessary
    	if (version_compare(PHP_VERSION, '5.2.11', '>=')) {
    		return $digHexStr;
    	}
    
    	// Workaround #48284 by swapping byte order
    	$boFixed = array();
    	$boFixed[0] = $digHexStr[6];
    	$boFixed[1] = $digHexStr[7];
    	$boFixed[2] = $digHexStr[4];
    	$boFixed[3] = $digHexStr[5];
    	$boFixed[4] = $digHexStr[2];
    	$boFixed[5] = $digHexStr[3];
    	$boFixed[6] = $digHexStr[0];
    	$boFixed[7] = $digHexStr[1];
    
    	return implode("", $boFixed);
    }
    
	/**
     * Make the directory strucuture for the given id
     *
     * @param string $id cache id
     * @return boolean true
     */
    protected function _recursiveMkdirAndChmod($id)
    {
        if ($this->_options['hashed_directory_level'] <=0) {
            return true;
        }
        $partsArray = $this->_path($id, true);
        foreach ($partsArray as $part) {
            if (!is_dir($part)) {
                @mkdir($part, $this->_options['hashed_directory_umask']);
                @chmod($part, $this->_options['hashed_directory_umask']); // see #ZF-320 (this line is required in some configurations)
            }
        }
        return true;
    }
    
	/**
     * Return the file content of the given file
     *
     * @param  string $file File complete path
     * @return string File content (or false if problem)
     */
    protected function _fileGetContents($file)
    {
        $result = false;
        if (!is_file($file)) {
            return false;
        }
        $f = @fopen($file, 'rb');
        if ($f) {
            $result = stream_get_contents($f);
            @fclose($f);
        }
        return $result;
    }
    
	/**
     * Put the given string into the given file
     *
     * @param  string $file   File complete path
     * @param  string $string String to put in file
     * @return boolean true if no problem
     */
    protected function _filePutContents($file, $string)
    {
        $result = false;
        $f = @fopen($file, 'ab+');
        if ($f) {
            fseek($f, 0);
            ftruncate($f, 0);
            $tmp = @fwrite($f, $string);
            if (!($tmp === FALSE)) {
                $result = true;
            }
            @fclose($f);
        }
        @chmod($file, $this->_options['cache_file_umask']);
        return $result;
    }
}
