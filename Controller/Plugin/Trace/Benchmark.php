<?php

class Shaw_Controller_Plugin_Trace_Benchmark
	extends Shaw_Controller_Plugin_Trace_Abstract
{
	protected $logWriter = null;
	
	public $description = 'arf';
	
	public $title = 'Benchmark';
	
	public function init()
	{
		// Add log listener
		Shaw_Log::getInstance()->addWriter(new Shaw_Benchmark_LogListener());
		
		// Add doctrine listener
		Doctrine_Manager::connection()->setListener(new Shaw_Benchmark_DoctrineProfiler());
	}
	
 // Format time in ms
    private function _formatTime($time)
    {
        return number_format($time * 1000, 3, '.', '') . ' ms';
    }
    
    // Format percentage.
    private function _formatPercent($percent)
    {
        return number_format($percent, 2, '.', '')." %";
    }
    
    // Format sql.
    private function _formatSql($mark)
    {
    	$query = $mark['metadata']['query'];
        $query = str_replace('`','',$query);
        $query = ' ' . $query;
        $keywords = explode(',', 'select,from,where,order by,group by,insert,into,update,inner,join,left,and,asc,desc,on,limit,as,in');
        $functions = explode(',', 'count,sum,case,when,then,else,end');
        $breakwords = explode(',','from,where,group by,order by,inner');
        
        $query = preg_replace('/AS\s+[\w_]+\s*/i', '', $query);
        
        foreach ($keywords as $keyword)
            if (preg_match("/[\s\(\)]+($keyword *)[\s\(\)]+/i", $query, $matches))
                $query = str_replace($matches[1], '<span style="color: green">' . strtoupper($matches[1]) . '</span>', $query);
        
        foreach ($functions as $function)
            if (preg_match("/[\s\(\)]+($function *)[\s\(\)]+/i", $query, $matches))
                $query = str_replace($matches[1], '<span style="color: blue">' . strtoupper($matches[1]) . '</span>', $query);
        
        foreach ($breakwords as $breakword)
            if (preg_match("/($breakword *)/i", $query, $matches))
                $query = str_replace($matches[1], '<br />' . strtoupper($matches[1]), $query);
        $query = substr($query, 1);
        if(!isset($mark['calls'])){$mark['calls'] = 1;}
        $callSteps = array();
        if(isset($mark['callSteps'])){ foreach($mark['callSteps'] as $name => $count){$callSteps[] = "$name:$count";}}
        return sprintf('<b>%s Calls</b>(%s) to<br />%s', $mark['calls'], join(', ',$callSteps), $query);
    }
    
    private function _formatLog($mark)
    {
    	return sprintf('<b>%s</b> (%s) %s', $mark['metadata']['priorityName'], $mark['metadata']['caller'], $mark['metadata']['message']);
    }
	
	public function render()
	{
        $marks = Shaw_Benchmark::getInstance()->getMarks();
        //var_dump($marks); die;
        // Head
        $out  = '<table border="1"><thead><tr>';
        $out .= '<th>name</th>';
        $out .= '<th align="center">duration (ms)</th>';
        $out .= '<th align="center">from start (ms)</th>';
        $out .= '<th align="center" colspan="2">subcomponents</th></tr></thead>';
        
        // Cntent        
        foreach ($marks as $mark) {
            $content = '';
        	switch($mark['name']){
        		case 'log':
        			$content = $this->_formatLog($mark);
        			break;
        		case 'sql':
        			$content = $this->_formatSql($mark);
        			break;
        		default:
        			$content = 'N/I';
        			break;
        	}
            
            $out .= '<tr><td>';
            $out .= join('</td><td>', array(
                str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;',$mark['level']) . $mark['name'],
                $this->_formatTime($mark['duration']),
                $this->_formatTime($mark['at']),
                $content
            ));
            $out .= '</td></tr>';
        }
        
        // Footer
        $out .= "</table>\n";
        
        $xhtml = $out;
        return $xhtml;
		
	}
}