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
		$this->_tmpPath = realpath(APPLICATION_PATH . '/../tmp');
		$this->_config = $config;
	}

	/**
	 * @param bool $return Returns result if true, saves it to file otherwise
	 * @param bool $structure If true, dump only one row for dataset creation
	 * @return string|void
	 */
	public function dumpTable($table, $return=false, $structure=false, $all=false)
	{
		switch($table) {
			case 'age':
				$sql = 'SELECT t.id, t.name FROM fbi_age t WHERE 1';
				break;
			case 'cities' :
				$sql = 'SELECT t.id, t.name FROM fbi_cities t LEFT JOIN fbi_rPagesCities pc ON (t.id=pc.idCity) WHERE pc.idPage = '.$this->_idPage;
				break;
			case 'days':
				$sql = 'SELECT t.id, t.date, t.dau, t.mau, t.views, t.viewsTotal, t.viewsUnique, t.viewsLogin, t.viewsLogout, '
					. 't.viewsMale, t.viewsFemale, t.viewsUnknownSex, t.likesTotal, t.likesAdded, t.likesRemoved, '
					. 't.contentLikesAdded, t.contentLikesRemoved, t.comments, t.feedViews, t.feedViewsUnique, t.wallPosts, '
					. 't.wallPostsUnique, t.photos, t.photoViews, t.photoViewsUnique, t.videos, t.videoPlays, t.videoPlaysUnique, '
					. 't.audioPlays, t.audioPlaysUnique, t.discussions, t.discussionsUnique, t.reviewsAdded, t.reviewsAddedUnique, '
					. 't.reviewsModified, t.reviewsModifiedUnique, t.idSnapshot AS snapshot '
					. 'FROM fbi_days t WHERE t.idPage ='.$this->_idPage;
				break;
			case 'daysCountries':
				$sql = 'SELECT t.id, t.idDay, t.country, t.views FROM fbi_daysCountries t WHERE t.idPage = '.$this->_idPage;
				break;
			case 'likes' :
				$sql = 'SELECT t.id, t.date, t.male, t.female, t.unknownSex FROM fbi_likes t WHERE t.idPage = '.$this->_idPage;
				break;
			case 'likesCountries' :
				$sql = 'SELECT t.id, t.idLike, t.country, t.likes FROM fbi_likesCountries t WHERE t.idPage = '.$this->_idPage;
				break;
			case 'rDaysAge' :
				$sql = 'SELECT t.id, t.idDay, t.idAge, t.views FROM fbi_rDaysAge t WHERE t.idPage = '.$this->_idPage;
				break;
			case 'rDaysCities' :
				$sql = 'SELECT t.id, t.idDay, t.idCity, t.views FROM fbi_rDaysCities t WHERE t.idPage = '.$this->_idPage;
				break;
			case 'rDaysReferrals' :
				$sql = 'SELECT t.id, t.idDay, t.idReferral, t.views FROM fbi_rDaysReferrals t WHERE t.idPage = '.$this->_idPage;
				break;
			case 'rLikesAge' :
				$sql = 'SELECT t.id, t.idLike, t.idAge, t.likes FROM fbi_rLikesAge t WHERE t.idPage = '.$this->_idPage;
				break;
			case 'rLikesCities' :
				$sql = 'SELECT t.id, t.idLike, t.idCity, t.likes FROM fbi_rLikesCities t WHERE t.idPage = '.$this->_idPage;
				break;
			case 'referrals':
				$sql = 'SELECT t.id, t.name, t.type FROM fbi_referrals t LEFT JOIN fbi_rPagesReferrals pr ON (t.id=pr.idReferral) WHERE pr.idPage = '.$this->_idPage;
				break;
			default:
				return false;
		}

		if ($structure) {
			$sql .= ' LIMIT 1';
		} elseif (!$all) {
			$sql .= ' AND t.timestamp > \''.date('Y-m-d H:i:s', strtotime('-4 days')).'\'';
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
	public function loadDataset($dataset, $all=true)
	{
		$this->dumpTable($dataset, false, false, $all);
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
		$this->_gd->createDate('FB_Date', false);
		
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
	public function loadData($all=false)
	{
		$this->loadDataset('days', $all);
		$this->loadDataset('daysCountries', $all);
		$this->loadDataset('referrals', $all);
		$this->loadDataset('age', $all);
		$this->loadDataset('cities', $all);
		$this->loadDataset('likes', $all);
		$this->loadDataset('likesCountries', $all);
		$this->loadDataset('rDaysAge', $all);
		$this->loadDataset('rDaysCities', $all);
		$this->loadDataset('rDaysReferrals', $all);
		$this->loadDataset('rLikesAge', $all);
		$this->loadDataset('rLikesCities', $all);

		$this->_gd->updateReports();
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
