<?php
/**
 * GoodData API class
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 29.6.11, 13:51
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
	 * @var id of GD project
	 */
	private $_idProject;

	
	/**
	 * @param $username
	 * @param $password
	 * @param $idProject
	 */
	public function __construct($username, $password, $idProject)
	{
		$this->_username = $username;
		$this->_password = $password;
		$this->_idProject = $idProject;
	}

	/**
	 * Common wrapper for GD CLI commands
	 * @param array $args
	 * @param bool $reportErrors
	 * @return void
	 */
	public function call($args, $reportErrors=true)
	{
		$command = self::CLI_PATH.' -u '.$this->_username.' -p '.$this->_password.' -e \'OpenProject(id="'.$this->_idProject.'");';
		$command .= $args;

		$output = shell_exec($command.'\'');

		if ($reportErrors && strpos($output, 'ERROR')) {
			App_Debug::send($output, null, 'http.log');
		}
		echo $output;

		system('rm ./*.log*');
	}

	/**
	 * Set of commands which create a date
	 * @param $name
	 * @param $includeTime
	 * @return void
	 */
	public function createDate($name, $includeTime)
	{
		echo "\n".'*** Create date: '.$name."\n";
		$command = 'UseDateDimension(name="'.$name.'", includeTime="'.($includeTime ? 'true' : 'false').'");';
		$command .= 'GenerateMaql(maqlFile="'.APPLICATION_PATH.'/../tmp/temp.maql");';
		$command .= 'ExecuteMaql(maqlFile="'.APPLICATION_PATH.'/../tmp/temp.maql");';

		$this->call($command);
		system('rm -rf '.APPLICATION_PATH.'/../tmp/temp.maql');
	}

	public function updateReports()
	{
		echo "\n".'*** Updating Reports'."\n";
		$command = 'GetReports(fileName="'.APPLICATION_PATH.'/../tmp/reports.txt");';
		$command .= 'ExecuteReports(fileName="'.APPLICATION_PATH.'/../tmp/reports.txt");';

		$this->call($command, false);
		system('rm -rf '.APPLICATION_PATH.'/../tmp/reports.txt');
	}

	/**
	 * Set of commands which create a dataset
	 * @param $xml
	 * @param $csv
	 * @return void
	 */
	public function createDataset($xml, $csv)
	{
		echo "\n".'*** Create dataset: '.basename($xml)."\n";
		$command = 'LoadCsv(csvDataFile="' . $csv . '", header="true", configFile="' . $xml . '");';
		$command .= 'GenerateMaql(maqlFile="'.APPLICATION_PATH.'/../tmp/temp.maql");';
		$command .= 'ExecuteMaql(maqlFile="'.APPLICATION_PATH.'/../tmp/temp.maql");';

		$this->call($command);
		system('rm -rf '.APPLICATION_PATH.'/../tmp/temp.maql');
	}

	/**
	 * Set of commands which create a dataset
	 * @param $xml
	 * @param $csv
	 * @return void
	 */
	public function updateDataset($xml, $csv)
	{
		echo "\n".'*** Update dataset: '.basename($xml)."\n";
		$command = 'LoadCsv(csvDataFile="' . $csv . '", header="true", configFile="' . $xml . '");';
		$command .= 'GenerateUpdateMaql(maqlFile="'.APPLICATION_PATH.'/../tmp/temp.maql");';
		$command .= 'ExecuteMaql(maqlFile="'.APPLICATION_PATH.'/../tmp/temp.maql");';

		$this->call($command);
		system('rm -rf '.APPLICATION_PATH.'/../tmp/temp.maql');
	}

	/**
	 * Set of commands which loads data to data set
	 * @param $xml
	 * @param $csv
	 * @return void
	 */
	public function loadData($xml, $csv, $incremental=false)
	{
		echo "\n".'*** Load data: '.basename($csv)."\n";
		$command = 'LoadCsv(csvDataFile="' . $csv . '", header="true", configFile="' . $xml . '");';
		$command .= 'TransferData(incremental="'.($incremental ? 'true' : 'false').'", waitForFinish="true");';

		$this->call($command);
	}

	/**
	 * Double all double quotes because of GoodData escaping
	 * @static
	 * @param $string
	 * @return string
	 */
	public static function escapeString($string)
	{
		return substr(trim(str_replace('"', '""', (string)$string)), 0, 255);
	}

}
