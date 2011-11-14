<?php
/**
 * GoodData API class
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-29
 * 
 */
 
class App_GoodData
{
	/**
	 *
	 */
	const CLI_PATH = '/opt/ebs-disk/GD/cli/bin/gdi.sh';


	/**
	 * @var GD username
	 */
	private $_username;

	/**
	 * @var GD password
	 */
	private $_password;

	/**
	 * @param $username
	 * @param $password
	 * @internal param $idProject
	 */
	public function __construct($username, $password)
	{
		$this->_username = $username;
		$this->_password = $password;
	}

	/**
	 * Common wrapper for GD CLI commands
	 * @param array $args
	 * @param bool $return
	 * @param bool $reportErrors
	 * @return void|string
	 */
	public function call($args, $return=TRUE, $reportErrors=TRUE)
	{
		$command = self::CLI_PATH.' -u '.$this->_username.' -p '.$this->_password.' -e \''.$args;
		$output = shell_exec($command.'\'');

		if (strpos($output, '503 Service Unavailable') || strpos($output, 'Error invoking GoodData WebDav API')) {
			sleep(60);
			$this->call($args, $reportErrors);
			App_Debug::log('GD Service Unavailable');
		} else {
			if ($reportErrors && strpos($output, 'ERROR')) {
				App_Debug::send($output, null, 'debug.log');
			}

			if($return) {
				return $output;
			} else {
				echo $output;
			}
			system('rm ./*.log*');
		}
	}

	/**
	 * Double all double quotes because of GoodData escaping
	 * @static
	 * @param $string
	 * @param bool $stripQuotes
	 * @return string
	 */
	public static function escapeString($string, $stripQuotes=FALSE)
	{
		if($stripQuotes) {
			$result = str_replace('"', '', $string);
		} else {
			$result = str_replace('"', '""', (string)$string);
		}
		$result = substr(trim($result), 0, 255);

		// remove trailing quotation mark if there is only one in the end of string
		if(substr($result, strlen($result)-1) == '"' && substr($result, strlen($result)-2) != '""') {
			$result = substr($result, 0, strlen($result)-1);
		}
		return $result;
	}

}
