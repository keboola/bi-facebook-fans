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
	 * Set of commands which create a date
	 * @param $name
	 * @param $includeTime
	 * @return void
	 */
	public function createDate($name, $includeTime)
	{
		$command = self::CLI_PATH.' -u '.$this->_username.' -p '.$this->_password.' -e \'OpenProject(id="'.$this->_idProject.'");';
		$command .= 'UseDateDimension(name="'.$name.'", includeTime="'.($includeTime ? 'true' : 'false').'");';
		$command .= 'GenerateMaql(maqlFile="'.APPLICATION_PATH.'/../tmp/temp.maql");';
		$command .= 'ExecuteMaql(maqlFile="'.APPLICATION_PATH.'/../tmp/temp.maql");';

		system($command.'\'');
		system('rm -rf '.APPLICATION_PATH.'/../tmp/temp.maql');
		system('rm ./*.log');
	}

	/**
	 * Set of commands which create a dataset
	 * @param $xml
	 * @param $csv
	 * @return void
	 */
	public function createDataset($xml, $csv)
	{
		$command = self::CLI_PATH.' -u '.$this->_username.' -p '.$this->_password.' -e \'OpenProject(id="'.$this->_idProject.'");';
		$command .= 'LoadCsv(csvDataFile="' . $csv . '", header="true", configFile="' . $xml . '");';
		$command .= 'GenerateMaql(maqlFile="'.APPLICATION_PATH.'/../tmp/temp.maql");';
		$command .= 'ExecuteMaql(maqlFile="'.APPLICATION_PATH.'/../tmp/temp.maql");';
		
		system($command.'\'');
		system('rm -rf '.APPLICATION_PATH.'/../tmp/temp.maql');
		system('rm ./*.log');
	}

	/**
	 * Set of commands which loads data to data set
	 * @param $xml
	 * @param $csv
	 * @return void
	 */
	public function loadData($xml, $csv)
	{
		$command = self::CLI_PATH.' -u '.$this->_username.' -p '.$this->_password.' -e \'OpenProject(id="'.$this->_idProject.'");';
		$command .= 'LoadCsv(csvDataFile="' . $csv . '", header="true", configFile="' . $xml . '");';
		$command .= 'TransferData(incremental="false", waitForFinish="true");';

		system($command.'\'');
		system('rm ./*.log');
	}

}
