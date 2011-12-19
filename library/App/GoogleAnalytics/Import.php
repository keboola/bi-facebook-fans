<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Import
 *
 * @author Miroslav Čillík <miroslav.cillik@keboola.com>
 */
class App_GoogleAnalytics_Import {

	protected $_gapi;
	protected $_profile;

	// Define Table names and appropriate Metrics and Dimensions here!
	protected $_metricsSet = array(
		'visits' => array(
			'visits',
			'newVisits',
			'pageviews',
			'uniquePageviews',
			'timeOnPage',
			'entrances',
			'exits',
			'bounces'
		),
		'campaign'	=> array(
			'visits',
			'newVisits',
			'pageviews',
			//'uniquePageviews',
			'timeOnPage',
			'entrances',
			'exits',
			'bounces'
		),
		'goals' => array()		
	);

	protected $_dimensionsSet = array(
		'visits' => array(
			'visitorType',
			'visitCount',
			'pagePath',
			'landingPagePath',
			'exitPagePath',
			'date',
			//'hour'
		),
		'campaign'	=> array(
			'adSlot',
			'medium',
			'campaign',
			'landingPagePath',
			'referralPath',
			'source',
			'date',
			//'hour'
		),
		'goals' => array(
			//'adSlot',
			'medium',
			'source',
			'landingPagePath',
			'referralPath',
			'date',
			//'hour'
		)		
	);

	public function  __construct($user, $profile)
	{
		$this->_account = $user;
		$this->_profile = $profile;
		$this->_gapi = new App_GoogleAnalytics($user->gaAccessToken);
		$this->_gapi->refreshToken($user->id);
	}

	public function run($since, $until)
	{
		$this->getData($since,$until);
	}

	public function getData($since, $until)
	{
		echo "********************* \n";
		echo "*** Importing ... \n";
		echo "\n";

		// Visits
		
		echo "*** Visits reports \n";
		$table = new Model_VisitsStats();		
		$this->_fetchResults('visits', 'visits', $since, $until, $table, 
			array('profile' => $this->_profile)
		);

		// Campaigns
		echo "*** Campaigns reports \n";
		$table = new Model_CampaignStats();
		$this->_fetchResults('campaign', 'campaign', $since, $until, $table,
			array('profile' => $this->_profile)
		);

		// Goals
		echo "*** Goals \n";
		$goals = $this->_gapi->getGoals($this->_profile->gaAccountId,
				$this->_profile->gaWebPropertyId, $this->_profile->gaProfileId);

		if ($goals['totalResults'] > 0) {

			$goalTable = new Model_Goal();
			$statsTable = new Model_GoalsStats();

			foreach($goals['items'] as $g) {
				// add new goals if necessary

				echo "*** +- Goal ID: " . $g['id'] . "\n";

				$goalRow = $goalTable->fetchRow(array(
					'gaGoalId=?' => $g['id'],
					'profileId=?' => $this->_profile->id
				));

				if (!$goalRow) {
					$gid = $goalTable->insert(array(
						'gaGoalId'	=> $g['id'],
						'name'	=> $g['name'],
						'value'	=> $g['value'],
						'type'	=> $g['type'],
						'active'	=> $g['active'],
						'profileId'	=> $this->_profile->id
					));
				} else {
					$gid = $goalRow->id;
				}

				$this->_metricsSet['goals'] = array(
					'goal'.$g['id'].'Starts',
					'goal'.$g['id'].'Completions',
					'goal'.$g['id'].'Value',
					'goal'.$g['id'].'ConversionRate'
				);

				$this->_fetchResults('goals', 'goals', $since, $until, $statsTable, array(
					'goalId'	=> $gid,
					'gaGoalId'	=> $g['id'],
					'profile'	=> $this->_profile
				));

			}			
		}

		echo "*** Import finished. \n";
		echo "********************* \n";
	}

	/**
	 * Fetch and save results to database
	 * handles paged results
	 *
	 * @param String $dimensions	- key of dimensionsSet array
	 * @param String $metrics		- key of metricsSet array
	 * @param String $since			- date since
	 * @param String $until			- date until
	 * @param Zend_Db_Table $table	- table to save results into
	 * @param Array $params			- params for the parseResults method
	 */
	protected function _fetchResults($dimensions, $metrics, $since, $until, $table, $tableParams = array())
	{
		// Delete records from last two days and replace with fresh data
		$yesterday = date('Y-m-d', strtotime('-1 day')) . ' 00:00:00';
		$today = date('Y-m-d') . ' 00:00:00';
		$table->delete(array('datetime=?' => $yesterday));
		$table->delete(array('datetime=?' => $today));

		// Get latest "snapshot"
		$since = $this->_checkDateSince($since, $table);

		$result = $this->_gapi->getData($this->_profile->gaProfileId,
				$this->_dimensionsSet[$dimensions], $this->_metricsSet[$metrics],
				null, null, $since, $until, 1, 10000);

		$table->saveResult($result, $tableParams);

		// Paging
		$params = $this->_gapi->getDataParameters();
		if ($params['totalResults'] > $params['itemsPerPage']) {
			echo "total results: " . $params['totalResults'] . "\n";
			$pages = ceil($params['totalResults'] / $params['itemsPerPage']);
			echo "pages: " . $pages . "\n";
			echo "per page: " . $params['itemsPerPage'] . "\n";

			for ($i=0; $i<$pages; $i++) {

				$start = ($i+1)*$params['itemsPerPage']+1;
				$end = $start+$params['itemsPerPage'];

				$result = $this->_gapi->getData($this->_profile->gaProfileId,
					$this->_dimensionsSet[$dimensions], $this->_metricsSet[$metrics],
					null, null, $since, $until, $start, $end);

				$table->saveResult($result, $tableParams);
			}
		}
	}

	/**
	 * Checks if the since date is larger then the last record in database
	 * if not, returns new since date
	 *
	 * @param String $since - old since date
	 * @param String $tableName - table where to check
	 */
	protected function _checkDateSince($since, $table)
	{
		$identifier = 'profileId';
		if ($table->info('name') == 'ga_goalsStats') {
			$identifier = 'goalId';
		}

		$maxDate = $table->getAdapter()->fetchOne(
			"SELECT MAX(datetime) as maxdate FROM " . $table->info('name') . " WHERE ".$identifier."=?",
			array($this->_profile->id)
		);

		// add 1 day to maxdate
		if ($since <= $maxDate) {
			$dt = new DateTime();
			$dt->setDate(date('Y', strtotime($maxDate)), date('m', strtotime($maxDate)), date('d', strtotime($maxDate)));
			$dt->modify(' +1 day');

			$since = $dt->format('Y-m-d');
		}

		return $since;
	}

	/**
	 * Utility function removes diacritics characters
	 *
	 * @param String $str - string to remove diacritics from
	 */
	public static function normalize($str)
	{
		$url = $str;
		$url = str_replace(array('"', '\''), '', $url);
		//$url = trim($url, "-");
		$url = iconv("utf-8", "us-ascii//TRANSLIT", $url);
		//$url = strtolower($url);
		//$url = preg_replace('~[^-a-z0-9_]+~', '', $url);
		return $url;
	}	

	/**
	 * @deprecated
	 * @param <type> $url
	 * @return <type>
	 */
	public static function decodeShortUrl($url) {
		$ch = @curl_init($url);
		@curl_setopt($ch, CURLOPT_HEADER, TRUE);
		@curl_setopt($ch, CURLOPT_NOBODY, TRUE);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$response = @curl_exec($ch);
		preg_match('/Location: (.*)\n/', $response, $a);
		if (!isset($a[1])) return $url;
		return $a[1];
	}

}
?>
