<?php
/**
 * Page row
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-08
 */

class Model_Row_Like extends Zend_Db_Table_Row_Abstract
{

	/**
	 * @param array $data
	 * @param string $date
	 */
	public function addAge($idPage, $data, $date)
	{
		$_a = new Model_Age();
		$_la = new Model_LikesAge();

		foreach ($data as $k => $v) {
			$r = $_a->fetchRow(array('name=?' => $k));
			if (!$r) {
				$id = $_a->insert(array('name' => $k));
			} else {
				$id = $r->id;
			}
			$la = $_la->fetchRow(array('idLike=?' => $this->id, 'idAge=?' => $id, 'idPage=?' => $idPage));
			if (!$la) {
				$_la->insert(array('idLike' => $this->id, 'idAge' => $id, 'idPage' => $idPage, 'likes' => $v));
			} else {
				if ($this->date < $date) {
					$la->likes = $v;
					$la->save();
				}
			}
		}
	}

	/**
	 * @param int $idPage
	 * @param array $data
	 * @param string $date
	 */
	public function addCities($idPage, $data, $date)
	{
		$_c = new Model_Cities();
		$_lc = new Model_LikesCities();
		$_pc = new Model_PagesCities();

		foreach ($data as $k => $v) {
			$r = $_c->fetchRow(array('name=?' => $k));
			if (!$r) {
				$id = $_c->insert(array('name' => $k));
			} else {
				$id = $r->id;
			}
			$lc = $_lc->fetchRow(array('idLike=?' => $this->id, 'idCity=?' => $id, 'idPage=?' => $idPage));
			if (!$lc) {
				$_lc->insert(array('idLike' => $this->id, 'idCity' => $id, 'idPage' => $idPage, 'likes' => $v));
				try {
					$_pc->insert(array('idPage' => $idPage, 'idCity' => $id));
				} catch(Exception $e) {
					// already in db
				}
			} else {
				if ($this->date < $date) {
					$lc->likes = $v;
					$lc->save();
				}
			}
		}
	}

	/**
	 * @param array $data
	 * @param string $date
	 */
	public function addCountries($idPage, $data, $date)
	{
		$_c = new Model_LikesCountries();

		foreach ($data as $k => $v) {
			$c = $_c->fetchRow(array('idLike=?' => $this->id, 'country=?' => $k, 'idPage=?' => $idPage));
			if (!$c) {
				$_c->insert(array('idLike' => $this->id, 'country' => $k, 'idPage' => $idPage, 'likes' => $v));
			} else {
				if ($this->date < $date) {
					$c->likes = $v;
					$c->save();
				}
			}
		}
	}
}