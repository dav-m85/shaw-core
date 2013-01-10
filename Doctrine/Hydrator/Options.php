<?php

class Shaw_Doctrine_Hydrator_Options
	extends Doctrine_Hydrator_Abstract
{
	public function hydrateResultSet($stmt)
	{
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		$graph = array();
		foreach($data as $datum){
			$k = array_keys($datum);
			$graph[$datum[$k[0]]] = $datum[$k[1]];
		}
		
		return $graph;
	}
}