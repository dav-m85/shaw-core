<?php

class Shaw_Benchmark_DoctrineProfiler 
implements Doctrine_Overloadable, IteratorAggregate, Countable
{
    /**
     * @param array $listeners      an array containing all availible listeners
     */
    private $listeners  = array('query',
                                'prepare',
                                'commit',
                                'rollback',
                                'connect',
                                'begintransaction',
                                'exec',
                                'execute');

    /**
     * @param array $events         an array containing all listened events
     */
    private $events     = array();

    /**
     * @param array $eventSequences         an array containing sequences of all listened events as keys
     */
    private $eventSequences = array();

    /**
     * constructor
     */
    public function __construct() {

    }

    /**
     * setFilterQueryType
     *
     * @param integer $filter
     * @return boolean
     */
    public function setFilterQueryType() {
                                             
    }                                         
    /**
     * method overloader
     * this method is used for invoking different listeners, for the full
     * list of availible listeners, see Doctrine_EventListener
     *
     * @param string $m     the name of the method
     * @param array $a      method arguments
     * @see Doctrine_EventListener
     * @return boolean
     */
    public function __call($m, $a)
    {
        // first argument should be an instance of Doctrine_Event
        if ( ! ($a[0] instanceof Doctrine_Event)) {
            throw new Doctrine_Connection_Profiler_Exception("Couldn't listen event. Event should be an instance of Doctrine_Event.");
        }
        
        if (substr($m, 0, 3) === 'pre') {
            // pre-event listener found
            $a[0]->start();
            
            $eventSequence = $a[0]->getSequence();
            if ( ! isset($this->eventSequences[$eventSequence])) {
                $this->events[] = $a[0];
                $this->eventSequences[$eventSequence] = true;
            }
        } else {
            // after-event listener found
            $a[0]->end();
            
            Shaw_Benchmark::mark('sql', array(
                'sql'   => $a[0]->getName(),
                'duration' => $a[0]->getElapsedSecs(),
                'query' => $a[0]->getQuery(),
                'params' => $a[0]->getParams()
            ));
        }
    }

    /**
     * get
     *
     * @param mixed $key
     * @return Doctrine_Event
     */
    public function get($key) 
    {
        if (isset($this->events[$key])) {
            return $this->events[$key];
        }
        return null;
    }

    /**
     * getAll
     * returns all profiled events as an array
     *
     * @return array        all events in an array
     */
    public function getAll() 
    {
        return $this->events;
    }

    /**
     * getIterator
     * returns an iterator that iterates through the logged events
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->events);
    }

    /**
     * count
     * 
     * @return integer
     */
    public function count() 
    {
        return count($this->events);
    }

    /**
     * pop the last event from the event stack
     *
     * @return Doctrine_Event
     */
    public function pop() 
    {
        $event = array_pop($this->events);
        if ($event !== null)
        {
            unset($this->eventSequences[$event->getSequence()]);
        }
        return $event;
    }

    /**
     * Get the Doctrine_Event object for the last query that was run, regardless if it has
     * ended or not. If the event has not ended, it's end time will be Null.
     *
     * @return Doctrine_Event
     */
    public function lastEvent()
    {
        if (empty($this->events)) {
            return false;
        }

        end($this->events);
        return current($this->events);
    }
}