<?php
/**
 * Class to send Facebook statistics to GoodData
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 29.6.11, 14:25
 *
 */

class App_FacebookGoodData
{
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
	 * @var int
	 */
	private $_idPage;

	/**
	 * @var Zend_Config
	 */
	private $_config;

	

	/**
	 * @param $username
	 * @param $password
	 * @param $idProject
	 */
	public function __construct($config, $idProject, $idPage)
	{
		$this->_gd = new App_GoodData($config->gooddata->username, $config->gooddata->password, $idProject);
		$this->_idPage = $idPage;
		$this->_xmlPath = realpath(APPLICATION_PATH . '/../gooddata');
		$this->_tmpPath = realpath(APPLICATION_PATH . '/../temp');
		$this->_config = $config;
	}

	/**
	 * @param bool $return Returns result if true, saves it to file otherwise
	 * @param bool $structure If true, dump only one row for dataset creation
	 * @return string|void
	 */
	public function dumpTable($table, $return=false, $structure=false)
	{
		switch($table) {
			case 'age':
				$sql = 'SELECT id, name FROM fbi_age';
				break;
			case 'cities' :
				$sql = 'SELECT c.id, c.name FROM fbi_cities c LEFT JOIN fbi_rPagesCities pc ON (c.id=pc.idCity) WHERE pc.idPage = '.$this->_idPage;
				break;
			case 'days':
				$sql = 'SELECT *, id AS snapshot FROM fbi_days WHERE idPage ='.$this->_idPage;
				break;
			case 'daysCountries':
				$sql = 'SELECT id, idDay, country, views FROM fbi_daysCountries WHERE idPage = '.$this->_idPage;
				break;
			case 'likes' :
				$sql = 'SELECT id, date, male, female, unknownSex FROM fbi_likes WHERE idPage = '.$this->_idPage;
				break;
			case 'likesCountries' :
				$sql = 'SELECT id, idLike, country, likes FROM fbi_likesCountries WHERE idPage = '.$this->_idPage;
				break;
			case 'rDaysAge' :
				$sql = 'SELECT id, idDay, idAge, views FROM fbi_rDaysAge WHERE idPage = '.$this->_idPage;
				break;
			case 'rDaysCities' :
				$sql = 'SELECT id, idDay, idCity, views FROM fbi_rDaysCities WHERE idPage = '.$this->_idPage;
				break;
			case 'rDaysReferrals' :
				$sql = 'SELECT id, idDay, idReferral, views FROM fbi_rDaysReferrals WHERE idPage = '.$this->_idPage;
				break;
			case 'rLikesAge' :
				$sql = 'SELECT id, idLike, idAge, likes FROM fbi_rLikesAge WHERE idPage = '.$this->_idPage;
				break;
			case 'rLikesCities' :
				$sql = 'SELECT id, idLike, idCity, likes FROM fbi_rLikesCities WHERE idPage = '.$this->_idPage;
				break;
			case 'referrals':
				$sql = 'SELECT r.id, r.name, r.type FROM fbi_referrals r LEFT JOIN fbi_rPagesReferrals pr ON (r.id=pr.idReferral) WHERE pr.idPage = '.$this->_idPage;
				break;
			default:
				return false;
		}

		if ($structure) {
			$sql .= ' LIMIT 1';
		}


		$file = null;
		if (!$return)
			$file = $this->_tmpPath.'/'.$table.'.csv';
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
	 * @return void
	 */
	public function loadDataset($dataset)
	{
		$this->dumpTable($dataset, false, false);
		$this->_gd->loadData($this->_xmlPath . '/'.$dataset.'.xml', $this->_tmpPath.'/'.$dataset.'.csv');
	}

	/**
	 * Updates dataset in GoodData
	 * @return void
	 */
	public function updateStructure($dataset)
	{
		$this->dumpTable($dataset, false, true);
		$this->_gd->updateDataset($this->_xmlPath . '/'.$dataset.'.xml', $this->_tmpPath.'/'.$dataset.'.csv');
	}

	/**
	 * Create data sets in GoodData
	 * @return void
	 */
	public function setup()
	{
		$this->_gd->createDate('FacebookDate', false);
		
		$this->createDataset('days');
		$this->createDataset('daysCountries');
		$this->createDataset('referrals');
		$this->createDataset('age');
		$this->createDataset('cities');
		$this->createDataset('likes');
		$this->createDataset('likesCountries');
		$this->createDataset('rDaysAge');
		$this->createDataset('rDaysCities');
		$this->createDataset('rDaysReferrals');
		$this->createDataset('rLikesAge');
		$this->createDataset('rLikesCities');
	}

	/**
	 * Loads data to all data sets in GoodData
	 * @return void
	 */
	public function loadData()
	{
		$this->loadDataset('days');
		$this->loadDataset('daysCountries');
		$this->loadDataset('referrals');
		$this->loadDataset('age');
		$this->loadDataset('cities');
		$this->loadDataset('likes');
		$this->loadDataset('likesCountries');
		$this->loadDataset('rDaysAge');
		$this->loadDataset('rDaysCities');
		$this->loadDataset('rDaysReferrals');
		$this->loadDataset('rLikesAge');
		$this->loadDataset('rLikesCities');
	}

	/**
	 * @param string $sql
	 * @param string $file Save output to file if set, return otherwise
	 * @return void|string
	 */
	public function dump($sql, $file='')
	{
		$command = 'mysql -u '.$this->_config->db->login.' -p'.$this->_config->db->password.' '
				.$this->_config->db->db.' -B -e "'.$sql.'" | sed \'s/\t/","/g;s/^/"/;s/$/"/;s/\n//g\'';

		if ($file) {
			$command .= ' > '.$file;
		}

		return shell_exec($command);
	}

}
