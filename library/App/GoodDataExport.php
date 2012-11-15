<?php
/**
 * Class to send Facebook statistics to GoodData
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 29.6.11, 14:25
 *
 */

class App_GoodDataExport
{

	/**
	 * Id of demo project where we send all data
	 */
	const DEMO_PROJECT = 1;

	/**
	 * @var string
	 */
	private $_xmlPath;

	/**
	 * @var string Path to temp dir for csv files
	 */
	private $_tmpPath;

	/**
	 * @var \App_GoodData
	 */
	private $_gd;

	/**
	 * @var Zend_Config
	 */
	private $_config;

	/**
	 * @var int
	 */
	private $_idUser;

	/**
	 * @var string
	 */
	private $_idProject;

	/**
	 * @var array
	 */
	private $_accounts;


	/**
	 * @param $idProject
	 * @param $idPage
	 * @param $config
	 */
	public function __construct($idProject, $idAccount, $config)
	{
		$this->_gd = new App_GoodData($config->gooddata->username, $config->gooddata->password, $idProject);

		$this->_idProject = $idProject;
		$this->_idAccount = $idAccount;
		$this->_config = $config;

		$this->_xmlPath = realpath(APPLICATION_PATH . '/../gooddata');
		$this->_tmpPath = realpath(APPLICATION_PATH . '/../tmp');

		//$_ua = new Model_UsersAccounts();
		//$this->_accounts = $_ua->accounts($this->_idUser);
	}

	public function escapeQuotesSQL($name, $alias=NULL)
	{
		if (!$alias) {
			if (strpos($name, '.')) {
				$alias = substr($name, strpos($name, '.')+1);
			} else {
				$alias = $name;
			}
		}
		return 'REPLACE('.$name.', \'\"\', \'\"\"\') AS '.$alias;
	}

	public function escapeStringSQL($name, $alias=NULL)
	{
		if (!$alias) {
			if (strpos($name, '.')) {
				$alias = substr($name, strpos($name, '.')+1);
			} else {
				$alias = $name;
			}
		}
		return 'SUBSTRING(REPLACE('.$name.', \'\"\', \'\'), 0, 127) AS '.$alias;
	}

	public function getSnapshotSQL($name='s.date', $alias='idSnapshot')
	{
		return 'CAST(UNIX_TIMESTAMP(DATE('.$name.'))/86400 AS UNSIGNED) AS '.$alias;
	}

	public function removeNull($column)
	{
		return 'IFNULL(' . $column . ', 0)';
	}

	/**
	 * @param $dataset
	 * @param bool $return
	 * @param bool $structure
	 * @param bool $all
	 * @return bool|string|void
	 */
	public function dumpTable($dataset, $return=false, $structure=false, $all=false)
	{
		$isDemo = $this->_idUser == self::DEMO_PROJECT;
		$prefix = $this->_config->db->prefix;

		switch($dataset) {
			case 'Friends':
				$sql = 'SELECT t.id, t.idFB, t.name, t.idUser FROM ' . $prefix . 'friends t '
						. 'LEFT JOIN ' . $prefix . 'rPagesUsers pu ON (t.idUser=pu.idUser) '
						. 'LEFT JOIN ' . $prefix . 'rAccountsPages ap ON (pu.idPage=ap.idPage) '
						. 'WHERE ap.idAccount = ' . $this->_idAccount;
				break;

			case 'Pages':
				$sql = 'SELECT t.id, t.idFB, t.name FROM ' . $prefix . 'pages t '
						. 'LEFT JOIN ' . $prefix . 'rAccountsPages ap ON (t.id=ap.idPage) '
						. 'WHERE ap.idAccount = ' . $this->_idAccount;
				break;

			case 'rPagesUsers' :
				$sql = 'SELECT t.id, t.idPage, t.idUser, t.lifetime, t.isLike FROM ' . $prefix . 'rPagesUsers t '
						. 'LEFT JOIN ' . $prefix . 'rAccountsPages ap ON (t.idPage=ap.idPage) '
						. 'WHERE ap.idAccount = ' . $this->_idAccount;
				break;

			case 'StatusMessages' :
				$sql = 'SELECT DISTINCT t.id, t.idFB, t.idUser, t.story, t.message, t.type, t.comments, t.action, DATE(t.datetime) as datetime, '
						. $this->getSnapshotSQL('t.datetime') . ' FROM ' . $prefix . 'statusMessages t '
						. 'LEFT JOIN ' . $prefix . 'rPagesUsers pu ON (t.idUser=pu.idUser) '
						. 'LEFT JOIN ' . $prefix . 'rAccountsPages ap ON (pu.idPage=ap.idPage) '
						. 'WHERE ap.idAccount = ' . $this->_idAccount;
				break;

			case 'Users' :
				$sql = 'SELECT DISTINCT t.id, t.idFB, t.name, t.email, t.accessToken FROM ' . $prefix . 'users t '
						. 'LEFT JOIN ' . $prefix . 'rPagesUsers pu ON (t.id=pu.idUser) '
						. 'LEFT JOIN ' . $prefix . 'rAccountsPages ap ON (pu.idPage=ap.idPage) '
						. 'WHERE ap.idAccount = ' . $this->_idAccount;				
				break;

			case 'Objects' :
				$sql = 'SELECT t.id, t.idFB, t.name, t.type FROM ' . $prefix . 'objects t '
						. 'LEFT JOIN ' . $prefix . 'rStatusMessagesObjects so ON (so.idObject = t.id) '
						. 'LEFT JOIN ' . $prefix . 'statusMessages s ON (so.idStatusMessage = s.id) '
						. 'LEFT JOIN ' . $prefix . 'rPagesUsers pu ON (pu.idUser=s.idUser) '
						. 'LEFT JOIN ' . $prefix . 'rAccountsPages ap ON (pu.idPage=ap.idPage) '
						. 'WHERE ap.idAccount = ' . $this->_idAccount;
				break;

			case 'rStatusMessagesObjects' :
				$sql = 'SELECT t.id, t.idStatusMessage, t.idObject FROM ' . $prefix . 'rStatusMessagesObjects t '
						. 'LEFT JOIN ' . $prefix . 'statusMessages s ON (t.idStatusMessage = s.id) '
						. 'LEFT JOIN ' . $prefix . 'rPagesUsers pu ON (pu.idUser=s.idUser) '
						. 'LEFT JOIN ' . $prefix . 'rAccountsPages ap ON (pu.idPage=ap.idPage) '
						. 'WHERE ap.idAccount = ' . $this->_idAccount;
				break;

			default:
				return false;
		}

		if ($structure) {
			$sql .= ' LIMIT 1';
		} elseif (!$all) {
			$sql .= ' AND t.timestamp > \'' . date('Y-m-d H:i:s', strtotime('-3 days')) . '\'';
		}

		$file = null;
		if (!$return)
			$file = $this->_tmpPath . '/' . $dataset . '.csv';
		return $this->dump($sql, $file);
	}

	/**
	 * @param $dataset
	 * @return void
	 */
	public function createDataset($dataset)
	{
		$this->dumpTable($dataset, false, true);
		$this->_gd->createDataset($this->_xmlPath . '/'.$dataset.'.xml', $this->_tmpPath.'/'.$dataset.'.csv');
	}

	/**
	 * @param $dataset
	 * @param bool $all
	 * @return void
	 */
	public function loadDataset($dataset, $all=true)
	{
		$this->dumpTable($dataset, false, false, $all);
		$this->_gd->loadData($this->_xmlPath . '/'.$dataset.'.xml', $this->_tmpPath.'/'.$dataset.'.csv', !$all);
	}

	/**
	 * Updates dataset in GoodData
	 * @param $dataset
	 * @return void
	 */
	public function updateStructure($dataset)
	{
		$this->dumpTable($dataset, false, true);
		$this->_gd->updateDataset($this->_xmlPath . '/'.$dataset.'.xml', $this->_tmpPath.'/'.$dataset.'.csv', $this->_idUser);
	}

	/**
	 * Create data sets in GoodData
	 * @return void
	 */
	public function setup()
	{
//		$this->_gd->createDate('KB_EventDate', TRUE);
//		$this->createDataset('Users');
//		$this->createDataset('Pages');
//		$this->createDataset('rPagesUsers');
//		$this->createDataset('rStatusMessagesObjects');
//		$this->createDataset('StatusMessages');
//		$this->createDataset('Friends');
		$this->createDataset('Objects');
//

		//$this->_gd->call('ExecuteMAQL(maqlFile="'.$this->_xmlPath.'/setup.maql");');
	}

	/**
	 * Loads data to all data sets in GoodData
	 * @param bool $all
	 * @return void
	 */
	public function loadData($all=false)
	{
		$this->loadDataset('Users', $all);
		$this->loadDataset('Pages', $all);
		$this->loadDataset('rPagesUsers', $all);
		$this->loadDataset('StatusMessages', $all);
		$this->loadDataset('Friends', $all);
		$this->loadDataset('Objects', $all);
		$this->loadDataset('rStatusMessagesObjects', $all);

		$this->_gd->updateReports();
	}


	public function idProject()
	{
		return $this->_idProject;
	}


	/**
	 * @param string $sql
	 * @param string $file Save output to file if set, return otherwise
	 * @return void
	 */
	public function dump($sql, $file='')
	{
		$command = 'mysql -u '.$this->_config->db->login
			. ' -p'.$this->_config->db->password
			. ' -h '.$this->_config->db->host
			. ' '.$this->_config->db->db.' -B -e "'
			. $sql.'" | sed \'s/\t/","/g;s/^/"/;s/$/"/;s/\n//g\'';

		if ($file) {
			$command .= ' > '.$file;
		}

		$output = shell_exec($command);
		if ($file && $output) {
			$log = Zend_Registry::get('log');
			$log->log('MySQL dump error', Zend_Log::ERR, array(
				'user'		=> $this->_idUser,
				'error'		=> $output
			));
		}

		if(!$file)
			return $output;
	}

	/**
	 * @return string
	 */
	public function importDashboard()
	{
		echo "\n*** Import dashboard\n";
		return App_GoodDataService::importDashboard(1, $this->_idProject);
	}

	/**
	 * @param $email
	 * @return string
	 */
	public function inviteUser($email)
	{
		echo "\n*** Invite user: $email\n";
		return App_GoodDataService::inviteUser($email, $this->_idProject);
	}



}
