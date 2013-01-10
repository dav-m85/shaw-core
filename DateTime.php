<?php

class Shaw_DateTime extends DateTime
{
    /**
     * Used for overriding 'now' value.
     */ 
    protected static $_now = null; // DateTime
    
    public static function factory($time = "now", $timezone = null)
    {
    	return new self($time, $timezone);
    }
    
    public static function setNow($value){
        if(! $value instanceof DateTime)
            throw new Exception('Please provide a DateTime valid object.');
        self::$_now = $value;
    }
    
    public static function getNow(){
        return self::$_now;
    }
    
    public static function now(){
        $now = new self();
        return $now;
    }
    
    public static function today(){
        $today = new self();
        $today->setTime(0,0,0);
        return $today;
    }
    
    public function setTimestamp( $timestamp )
    {
        $date = getdate( ( int ) $timestamp );
        $this->setDate( $date['year'] , $date['mon'] , $date['mday'] );
        $this->setTime( $date['hours'] , $date['minutes'] , $date['seconds'] );
    }
    
    public function getTimestamp()
    {
        return $this->format( 'U' );
    }
    
    public function __construct($time = "now", $timezone = null){
        if($time instanceof DateTime || $time instanceof Shaw_DateTime){
            $time = $time->format('Y-m-d H:i:s');
        }
        else if($now = self::$_now){
            $time = $now->format('Y-m-d H:i:s');
            $timezone = $now->getTimezone();
            Shaw_Log::debug('Now is not today !');
        }
        if($timezone)
            parent::__construct($time, $timezone);
        else
            parent::__construct($time);
    }
    
    
    
    /**
     * Return Date in ISO8601 format
     *
     * @return String
     */
    public function __toString() {
        return $this->format('Y-m-d H:i:s');
    }

    /**
     * Return difference between $this and $date. Positive if $date is in the futur.
     */
    public function diff(DateTime $date, $span = 86400) {
        return (float)($this->format('U') - $date->format('U')) / $span;
    }

    /**
     * Return Age in Years
     *
     * @param Datetime|String $now
     * @return Integer
     */
    // USELESS
    public function getAge($now = 'NOW') {
        return $this->diff($now)->format('%y');
    }
    
    /**
     *
     * @ return Shaw_DateTime fluent interface.
     */
    public function setTime($hour , $minute , $second = 0 ){
        parent::setTime($hour, $minute, $second);
        return $this;
    }
    /*
    public function getZendDate(){
        // TODO
    }
    
    public function __toString(){
        return $this->format('d/m/Y');
    }
    */
    public function modify($string){
        parent::modify($string);
        return $this;
    }
    
    /**
     * Modify the date to be the next monday.
     *
     * Please note that if we are monday, it'll return the next one !
     */ 
    public function nextMonday(){
        $w = $this->format('w');
	if($w != 1){
	    $inc = 0;
	    if($w == 0)
		    $inc = 1;
	    else
		    $inc = 8 - $w;
		    
	    $this->modify('+'.$inc.' day');
	}
        else
            $this->modify('+1 week');
        return $this;
    }
}