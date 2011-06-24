<?php
/**
 * Class for debugging
 */
class App_Debug
{
	/**
	 * Method adds log entry to chosen file
	 * @param mixed $data
	 * @param string $file
	 */
	public static function log($data, $file='debug.log')
	{
		$output = date("Y-m-d H:i:s")."\n";
		$output .= print_r($data, true);
		
		error_log(
			$output."\n",
			3,
			APPLICATION_PATH . '/../logs/'.$file
		);
	}
}