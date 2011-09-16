<?

class App_Import
{
	private $_fbi;

	private $_page;

	public function __construct($page)
	{
		$this->_page = $page;
		$this->_fbi = new App_FacebookInsights($page);
	}

	public function run($since, $until)
	{
		$_l = new Model_Likes();
		
		$data = $this->_fbi->getData($since, $until);

		if (isset($data['lifetime'])) {
			$lifetime = $data['lifetime'];
			$data = array_diff_key($data, array('lifetime'));

			$monthDay = date('Y-m-01', strtotime($lifetime['date']));
			$l = $_l->fetchRow(array('idPage=?' => $this->_page->id, 'month=?' => $monthDay));
			if (!$l) {
				$lId = $_l->insert(array(
					'idPage'		=> $this->_page->id,
					'month'			=> $monthDay,
					'date'			=> $lifetime['date'],
					'male'			=> isset($lifetime['likesMale']) ? $lifetime['likesMale'] : 0,
					'female'		=> isset($lifetime['likesFemale']) ? $lifetime['likesFemale'] : 0,
					'unknownSex'	=> isset($lifetime['likesUnknownSex']) ? $lifetime['likesUnknownSex'] : 0
				));
				$l = $_l->fetchRow(array('id=?' => $lId));
			} else {
				if ($l->date <= $lifetime['date']) {
					$l->date = $lifetime['date'];
					$l->male = isset($lifetime['likesMale']) ? $lifetime['likesMale'] : 0;
					$l->female = isset($lifetime['likesFemale']) ? $lifetime['likesFemale'] : 0;
					$l->unknownSex = isset($lifetime['likesUnknownSex']) ? $lifetime['likesUnknownSex'] : 0;
					$l->save();
				}
			}

			if (isset($lifetime['countries'])) {
				$l->addCountries($this->_page->id, $lifetime['countries'], $lifetime['date']);
			}
			if (isset($lifetime['cities'])) {
				$l->addCities($this->_page->id, $lifetime['cities'], $lifetime['date']);
			}
			if (isset($lifetime['age'])) {
				$l->addAge($this->_page->id, $lifetime['age'], $lifetime['date']);
			}
		}


		$_d = new Model_Days();
		$_s = new Model_Snapshots();
		//sort by date key to have snapshots ordered by date
		ksort($data);
		foreach($data as $date => $values) if ($date != 'lifetime') {
			$age = isset($values['age']) ? $values['age'] : array();
			$activeUsersCity = isset($values['activeUsersCity']) ? $values['activeUsersCity'] : array();
			$activeUsersCountry = isset($values['activeUsersCountry']) ? $values['activeUsersCountry'] : array();
			$internalReferrals = isset($values['internalReferrals']) ? $values['internalReferrals'] : array();
			$externalReferrals = isset($values['externalReferrals']) ? $values['externalReferrals'] : array();

			$idSnapshot = $_s->add($date);

			$day = $_d->add(array_merge(
				array('date' => $date, 'idPage' => $this->_page->id, 'idSnapshot' => $idSnapshot),
				array_diff_key($values, array(
					'age' => null,
					'activeUsersCity' => null,
					'activeUsersCountry' => null,
					'internalReferrals' => null,
					'externalReferrals' => null
				))
			));
			$day->addAge($this->_page->id, $age);
			$day->addCities($this->_page->id, $activeUsersCity);
			$day->addCountries($this->_page->id, $activeUsersCountry);
			$day->addReferrals($this->_page->id, $internalReferrals, $externalReferrals);
		}

		return true;
	}
}