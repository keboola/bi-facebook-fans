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
	 * @var \App_GoodData
	 */
	private $_gd;

	/**
	 * @var int
	 */
	private $_idPage;

	/**
	 * @param $username
	 * @param $password
	 * @param $idProject
	 */
	public function __construct($username, $password, $idProject, $idPage)
	{
		$this->_gd = new App_GoodData($username, $password, $idProject);
		$this->_idPage = $idPage;
		$this->_xmlPath = realpath(APPLICATION_PATH . '/../gooddata');
	}

	/**
	 * Create data sets in GoodData
	 * @return void
	 */
	public function setup()
	{
		$this->_gd->createDate('FacebookDate', false);
		
		$this->_gd->createDataset($this->_xmlPath . '/days.xml', $this->dumpDays());
		$this->_gd->createDataset($this->_xmlPath . '/daysCountries.xml', $this->dumpDaysCountries());
		$this->_gd->createDataset($this->_xmlPath . '/referrals.xml', $this->dumpReferrals());
		$this->_gd->createDataset($this->_xmlPath . '/age.xml', $this->dumpAge());
		$this->_gd->createDataset($this->_xmlPath . '/cities.xml', $this->dumpCities());
		$this->_gd->createDataset($this->_xmlPath . '/likes.xml', $this->dumpLikes());
		$this->_gd->createDataset($this->_xmlPath . '/likesCountries.xml', $this->dumpLikesCountries());
		$this->_gd->createDataset($this->_xmlPath . '/rDaysAge.xml', $this->dumpDaysAge());
		$this->_gd->createDataset($this->_xmlPath . '/rDaysCities.xml', $this->dumpDaysCities());
		$this->_gd->createDataset($this->_xmlPath . '/rLikesAge.xml', $this->dumpLikesAge());
		$this->_gd->createDataset($this->_xmlPath . '/rLikesCities.xml', $this->dumpLikesCities());
	}

	/**
	 * Loads data to all data sets in GoodData
	 * @return void
	 */
	public function loadData()
	{
		$this->_gd->loadData($this->_xmlPath . '/days.xml', $this->dumpDays());
		$this->_gd->loadData($this->_xmlPath . '/daysCountries.xml', $this->dumpDaysCountries());
		$this->_gd->loadData($this->_xmlPath . '/referrals.xml', $this->dumpReferrals());
		$this->_gd->loadData($this->_xmlPath . '/age.xml', $this->dumpAge());
		$this->_gd->loadData($this->_xmlPath . '/cities.xml', $this->dumpCities());
		$this->_gd->loadData($this->_xmlPath . '/likes.xml', $this->dumpLikes());
		$this->_gd->loadData($this->_xmlPath . '/likesCountries.xml', $this->dumpLikesCountries());
		$this->_gd->loadData($this->_xmlPath . '/rDaysAge.xml', $this->dumpDaysAge());
		$this->_gd->loadData($this->_xmlPath . '/rDaysCities.xml', $this->dumpDaysCities());
		$this->_gd->loadData($this->_xmlPath . '/rLikesAge.xml', $this->dumpLikesAge());
		$this->_gd->loadData($this->_xmlPath . '/rLikesCities.xml', $this->dumpLikesCities());
	}



	/**
	 * @param bool $return Flag whether to print the data to output or save it to the file
	 * @return string
	 */
	public function dumpAge($return=false)
	{
		$_t = new Model_Age();
		$output = '"id","name"' . "\n";
		foreach ($_t->fetchAll() as $r) {
			$output .=
				'"' . $r->id . '",'
				. '"' . $r->name . '"'
				. "\n";
		}
		if ($return) {
			return $output;
		} else {
			$path = APPLICATION_PATH.'/../tmp/age.csv';
			file_put_contents($path, $output);
			return $path;
		}
	}

	/**
	 * @param bool $return Flag whether to print the data to output or save it to the file
	 * @return void|string
	 */
	public function dumpCities($return=false)
	{
		$_t = new Model_Cities();
		$output = '"id","name"' . "\n";
		foreach ($_t->fetchForPage($this->_idPage) as $r) {
			$output .=
				'"' . $r->id . '",'
				. '"' . $r->name . '"'
				. "\n";
		}
		if ($return) {
			return $output;
		} else {
			$path = APPLICATION_PATH.'/../tmp/cities.csv';
			file_put_contents($path, $output);
			return $path;
		}
	}

	/**
	 * @param bool $return Flag whether to print the data to output or save it to the file
	 * @return void|string
	 */
	public function dumpDays($return=false)
	{
		$_t = new Model_Days();
		$output = '"id","date","dau","mau","views","viewsunique","viewslogin","viewslogout","viewstotal","viewsmale",'
			. '"viewsfemale","viewsunknownsex","likestotal","likesadded","likesremoved","contentlikesadded",'
			. ',"contentlikesremoved","comments","feedviews","feedviewsunique","wallposts","wallpostsunique",'
			. '"photos","photoviews","photoviewsunique","videos","videoplays","videoplaysunique","audioplays",'
			. '"audioplaysunique","discussions","discussionsunique","reviewsadded","reviewsaddedunique",'
			. '"reviewsaddedunique","reviewsmodified","reviewsmodifiedunique"' . "\n";
		foreach ($_t->fetchAll(array('idPage=?' => $this->_idPage)) as $r) {
			$output .= '"' . $r->id . '",'
				. '"' . $r->date . '",'
				. '"' . $r->dau . '",'
				. '"' . $r->mau . '",'
				. '"' . $r->views . '",'
				. '"' . $r->viewsUnique . '",'
				. '"' . $r->viewsLogin . '",'
				. '"' . $r->viewsLogout . '",'
				. '"' . $r->totalViews() . '",'
				. '"' . $r->viewsMale . '",'
				. '"' . $r->viewsFemale . '",'
				. '"' . $r->viewsUnknownSex . '",'
				. '"' . $r->likesTotal . '",'
				. '"' . $r->likesAdded . '",'
				. '"' . $r->likesRemoved . '",'
				. '"' . $r->contentLikesAdded . '",'
				. '"' . $r->contentLikesRemoved . '",'
				. '"' . $r->comments . '",'
				. '"' . $r->feedViews . '",'
				. '"' . $r->feedViewsUnique . '",'
				. '"' . $r->wallPosts . '",'
				. '"' . $r->wallPostsUnique . '",'
				. '"' . $r->photos . '",'
				. '"' . $r->photoViews . '",'
				. '"' . $r->photoViewsUnique . '",'
				. '"' . $r->videos . '",'
				. '"' . $r->videoPlays . '",'
				. '"' . $r->videoPlaysUnique . '",'
				. '"' . $r->audioPlays . '",'
				. '"' . $r->audioPlaysUnique . '",'
				. '"' . $r->discussions . '",'
				. '"' . $r->discussionsUnique . '",'
				. '"' . $r->reviewsAdded . '",'
				. '"' . $r->reviewsAddedUnique . '",'
				. '"' . $r->reviewsModified . '",'
				. '"' . $r->reviewsModifiedUnique . '"'
				. "\n";
		}
		if ($return) {
			return $output;
		} else {
			$path = APPLICATION_PATH.'/../tmp/days.csv';
			file_put_contents($path, $output);
			return $path;
		}
	}

	/**
	 * @param bool $return Flag whether to print the data to output or save it to the file
	 * @return void|string
	 */
	public function dumpDaysAge($return=false)
	{
		$_t = new Model_DaysAge();
		$i = 1;
		$output = '"id","idday","idage","views"' . "\n";
		foreach ($_t->fetchForPage($this->_idPage) as $r) {
			$output .=
				'"' . $i . '",'
				. '"' . $r->idDay . '",'
				. '"' . $r->idAge . '",'
				. '"' . $r->views . '"'
				. "\n";
			$i++;
		}
		if ($return) {
			return $output;
		} else {
			$path = APPLICATION_PATH.'/../tmp/rDaysAge.csv';
			file_put_contents($path, $output);
			return $path;
		}
	}

	/**
	 * @param bool $return Flag whether to print the data to output or save it to the file
	 * @return void|string
	 */
	public function dumpDaysCities($return=false)
	{
		$_t = new Model_DaysCities();
		$i = 1;
		$output = '"id","idday","idcity","views"' . "\n";
		foreach ($_t->fetchForPage($this->_idPage) as $r) {
			$output .= '"' . $i . '",'
				. '"' . $r->idDay . '",'
				. '"' . $r->idCity. '",'
				. '"' . $r->views . '"'
				. "\n";
			$i++;
		}
		if ($return) {
			return $output;
		} else {
			$path = APPLICATION_PATH.'/../tmp/rDaysCities.csv';
			file_put_contents($path, $output);
			return $path;
		}
	}

	/**
	 * @param bool $return Flag whether to print the data to output or save it to the file
	 * @return void|string
	 */
	public function dumpDaysCountries($return=false)
	{
		$_t = new Model_DaysCountries();
		$output = '"id","idday","country","views"' . "\n";
		foreach ($_t->fetchForPage($this->_idPage) as $r) {
			$output .= '"' . $r->id . '",'
				. '"' . $r->idDay . '",'
				. '"' . $r->country . '",'
				. '"' . $r->views . '"'
				. "\n";
			}
		if ($return) {
			return $output;
		} else {
			$path = APPLICATION_PATH.'/../tmp/daysCountries.csv';
			file_put_contents($path, $output);
			return $path;
		}
	}

	/**
	 * @param bool $return Flag whether to print the data to output or save it to the file
	 * @return void|string
	 */
	public function dumpDaysReferrals($return=false)
	{
		$_t = new Model_DaysReferrals();
		$i = 1;
		$output = '"id","idday","idreferral","views"' . "\n";
		foreach ($_t->fetchForPage($this->_idPage) as $r) {
			$output .= '"' . $i . '",'
				. '"' . $r->idDay . '",'
				. '"' . $r->idReferral . '",'
				. '"' . $r->views . '"'
				. "\n";
			$i++;
		}
		if ($return) {
			return $output;
		} else {
			$path = APPLICATION_PATH.'/../tmp/rDaysReferrals.csv';
			file_put_contents($path, $output);
			return $path;
		}
	}

	/**
	 * @param bool $return Flag whether to print the data to output or save it to the file
	 * @return void|string
	 */
	public function dumpLikes($return=false)
	{
		$_t = new Model_Likes();
		$output = '"id","date","male","female","unknownsex"' . "\n";
		foreach ($_t->fetchAll(array('idPage=?' => $this->_idPage)) as $r) {
			$output .=
				'"' . $r->id . '",'
				. '"' . $r->date . '",'
				. '"' . $r->male . '",'
				. '"' . $r->female . '",'
				. '"' . $r->unknownSex . '"'
				. "\n";
		}
		if ($return) {
			return $output;
		} else {
			$path = APPLICATION_PATH.'/../tmp/likes.csv';
			file_put_contents($path, $output);
			return $path;
		}
	}

	/**
	 * @param bool $return Flag whether to print the data to output or save it to the file
	 * @return void|string
	 */
	public function dumpLikesAge($return=false)
	{
		$_t = new Model_LikesAge();
		$i = 1;
		$output = '"id","idlike","idage","likes"' . "\n";
		foreach ($_t->fetchForPage($this->_idPage) as $r) {
			$output .=
				'"' . $i . '",'
				. '"' . $r->idLike . '",'
				. '"' . $r->idAge . '",'
				. '"' . $r->likes . '"'
				. "\n";
			$i++;
		}
		if ($return) {
			return $output;
		} else {
			$path = APPLICATION_PATH.'/../tmp/rLikesAge.csv';
			file_put_contents($path, $output);
			return $path;
		}
	}

	/**
	 * @param bool $return Flag whether to print the data to output or save it to the file
	 * @return void|string
	 */
	public function dumpLikesCities($return=false)
	{
		$_t = new Model_LikesCities();
		$i = 1;
		$output = '"id","idlike","idcity","likes"' . "\n";
		foreach ($_t->fetchForPage($this->_idPage) as $r) {
			$output .=
				'"' . $i . '",'
				. '"' . $r->idLike . '",'
				. '"' . $r->idCity . '",'
				. '"' . $r->likes . '"'
				. "\n";
			$i++;
		}
		if ($return) {
			return $output;
		} else {
			$path = APPLICATION_PATH.'/../tmp/rLikesCities.csv';
			file_put_contents($path, $output);
			return $path;
		}
	}

	/**
	 * @param bool $return Flag whether to print the data to output or save it to the file
	 * @return void|string
	 */
	public function dumpLikesCountries($return=false)
	{
		$_t = new Model_LikesCountries();
		$output = '"id","idlike","country","likes"' . "\n";
		foreach ($_t->fetchForPage($this->_idPage) as $r) {
			$output .=
				'"' . $r->id . '",'
				. '"' . $r->idLike . '",'
				. '"' . $r->country . '",'
				. '"' . $r->likes . '"'
				. "\n";
		}
		if ($return) {
			return $output;
		} else {
			$path = APPLICATION_PATH.'/../tmp/likesCountries.csv';
			file_put_contents($path, $output);
			return $path;
		}
	}

	/**
	 * @param bool $return Flag whether to print the data to output or save it to the file
	 * @return void|string
	 */
	public function dumpReferrals($return=false)
	{
		$_t = new Model_Referrals();
		$output = '"id","name","type"' . "\n";
		foreach ($_t->fetchForPage($this->_idPage) as $r) {
			$output .=
				'"' . $r->id . '",'
				. '"' . $r->name . '",'
				. '"' . $r->type . '"'
				. "\n";
		}
		if ($return) {
			return $output;
		} else {
			$path = APPLICATION_PATH.'/../tmp/referrals.csv';
			file_put_contents($path, $output);
			return $path;
		}
	}






}
