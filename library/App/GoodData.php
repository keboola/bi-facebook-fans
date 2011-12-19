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
	 * Clone project from template or create empty
	 * @param $name
	 * @param null $templateUri
	 * @return bool|string
	 */
	public function createProject($name, $templateUri=NULL)
	{
		$template = NULL;
		if($templateUri) {
			$template = 'templateUri="'.$templateUri.'"';
		}
		$output = $this->call('CreateProject(name="'.$name.'"'.$template.');');

		if(!strpos($output, 'ERROR')) {
			$start = strpos($output, 'id = \'');
			$end = strpos($output, '\' created.');
			return trim(substr($output, $start+6, $end-$start-6));
		} else {
			return FALSE;
		}
	}

	/**
	 * @param $idProject
	 * @param $email
	 * @param string $role
	 * @param null $text
	 * @return bool
	 */
	public function inviteUser($idProject, $email, $role="editor", $text=null)
	{
		$output = $this->_gd->call(
			'OpenProject(id="'.$idProject.'"); ' .
			'InviteUser(email="'.$email.'", msg="'.$text.'", role="'.$role.'");'
		);

		if(!strpos($output, 'ERROR')) {
			return TRUE;
		} else {
			return FALSE;
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
