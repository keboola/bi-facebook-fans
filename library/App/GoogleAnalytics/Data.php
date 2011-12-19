<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DataEntry
 *
 * @author Miroslav Čillík <miroslav.cillik@keboola.com>
 */
class App_GoogleAnalytics_Data {

	private $_metrics = array();
	private $_dimensions = array();
	private $_fbDb;

	public function __construct($metrics,$dimesions)
	{
		$this->_metrics = $metrics;
		$this->_dimensions = $dimesions;

		$registry = Zend_Registry::getInstance();
		$this->_fbDb = $registry->get('fbDb');
	}

	/**
	 * toString function to return the name of the result
	 * this is a concatented string of the dimesions chosen
	 *
	 * For example:
     * 'Firefox 3.0.10' from browser and browserVersion
	 *
	 * @return String
	 */
	public function __toString()
	{
		if(is_array($this->_dimensions)) {
			return implode(' ',$this->_dimensions);
		} else {
			return '';
		}
	}

	public function getFbDb()
	{
		return $this->_fbDb;
	}

	/**
	 * Get an associative array of the dimesions
	 * and the matching values for the current result
	 *
	 * @return Array
	 */
	public function getDimesions()
	{
		return $this->_dimensions;
	}

	/**
	 * Get an array of the metrics and the matchning
	 * values for the current result
	 *
	 * @return Array
	 */
	public function getMetrics()
	{
		return $this->_metrics;
	}

	/**
	 * Call method to find a matching metric or dimension to return
	 *
	 * @param $name String name of function called
	 * @return String
	 * @throws Exception if not a valid metric or _dimensions, or not a 'get' function
	 */
	public function __call($name, $parameters)
	{
		if(!preg_match('/^get/',$name)) {
			throw new Exception('No such function "' . $name . '"');
		}

		$name = preg_replace('/^get/','',$name);

		$metricKey = App_GoogleAnalytics::arrayKeyExistsNc($name,$this->_metrics);

		if ($metricKey) {
			return $this->_metrics[$metricKey];
		}

		$dimensionKey = App_GoogleAnalytics::arrayKeyExistsNc($name,$this->_dimensions);

		if($dimensionKey) {
			return $this->_dimensions[$dimensionKey];
		}

		throw new Exception('No valid metric or dimesion called "' . $name . '"');
	}

	/**
	 * Returns date (or date time if hour dimension is in the dimension array)
	 * formatted to GoodDate friendly format
	 *
	 * @return string 
	 */
	public function getDateFormatted()
	{
		$dateKey = App_GoogleAnalytics::arrayKeyExistsNc('date',$this->_dimensions);
		$date = $this->_dimensions[$dateKey];

		if ($date == '00000000') {
			$date = '19000101';
		}

		$hour = '00';

		if ($hourKey = App_GoogleAnalytics::arrayKeyExistsNc('hour',$this->_dimensions)) {
			$hour = $this->_dimensions[$hourKey];
		}		

		$result = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2) . ' ' . $hour . ':00:00';
		//echo $result . "\n";
		return $result;

	}	

}
?>
