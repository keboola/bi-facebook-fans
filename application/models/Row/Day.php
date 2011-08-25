<?php
/**
 * Dayily page stat row
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-08
 */

class Model_Row_Day extends Zend_Db_Table_Row_Abstract
{

	/**
	 * @param array $data
	 */
	public function addAge($idPage, $data)
	{
		$_a = new Model_Age();
		$_da = new Model_DaysAge();

		foreach ($data as $k => $v) {
			$r = $_a->fetchRow(array('name=?' => $k));
			if (!$r) {
				$id = $_a->insert(array('name' => $k));
			} else {
				$id = $r->id;
			}
			if (!$_da->fetchRow(array('idDay=?' => $this->id, 'idAge=?' => $id, 'idPage=?' => $idPage))) {
				$_da->insert(array('idDay' => $this->id, 'idAge' => $id, 'idPage' => $idPage, 'views' => $v));
			}
		}
	}

	/**
	 * @param int $idPage
	 * @param array $data
	 */
	public function addCities($idPage, $data)
	{
		$_c = new Model_Cities();
		$_dc = new Model_DaysCities();
		$_pc = new Model_PagesCities();

		foreach ($data as $k => $v) {
			$r = $_c->fetchRow(array('name=?' => $k));
			if (!$r) {
				$id = $_c->insert(array('name' => $k));
			} else {
				$id = $r->id;
			}
			if (!$_dc->fetchRow(array('idDay=?' => $this->id, 'idCity=?' => $id, 'idPage=?' => $idPage))) {
				$_dc->insert(array('idDay' => $this->id, 'idCity' => $id, 'idPage' => $idPage, 'views' => $v));
				try {
					$_pc->insert(array('idPage' => $idPage, 'idCity' => $id));
				} catch(Exception $e) {
					// already in db
				}
			}
		}
	}

	/**
	 * @param array $data
	 */
	public function addCountries($idPage, $data)
	{
		$_uc = new Model_DaysCountries();

		foreach ($data as $k => $v) {
			if (!$_uc->fetchRow(array('idDay=?' => $this->id, 'country=?' => $k, 'idPage=?' => $idPage))) {
				$_uc->insert(array('idDay' => $this->id, 'country' => $k, 'idPage' => $idPage, 'views' => $v));
			}
		}
	}

	/**
	 * @param int $idPage
	 * @param array $internal
	 * @param array $external
	 */
	public function addReferrals($idPage, $internal, $external)
	{
		$_r = new Model_Referrals();
		$_dr = new Model_DaysReferrals();
		$_pr = new Model_PagesReferrals();

		foreach ($internal as $k => $v) {
			$r = $_r->fetchRow(array('name=?' => $k));
			if (!$r) {
				$id = $_r->insert(array('name' => $k, 'type' => 'internal'));
			} else {
				$id = $r->id;
			}
			if (!$_dr->fetchRow(array('idDay=?' => $this->id, 'idReferral=?' => $id, 'idPage=?' => $idPage))) {
				$_dr->insert(array('idDay' => $this->id, 'idReferral' => $id, 'idPage' => $idPage, 'views' => $v));
				try {
					$_pr->insert(array('idPage' => $idPage, 'idReferral' => $id));
				} catch(Exception $e) {
					// already in db
				}
			}
		}
		foreach ($external as $k => $v) {
			$r = $_r->fetchRow(array('name=?' => $k));
			if (!$r) {
				$id = $_r->insert(array('name' => $k, 'type' => 'external'));
			} else {
				$id = $r->id;
			}
			if (!$_dr->fetchRow(array('idDay=?' => $this->id, 'idReferral=?' => $id, 'idPage=?' => $idPage))) {
				$_dr->insert(array('idDay' => $this->id, 'idReferral' => $id, 'idPage' => $idPage, 'views' => $v));
				try {
					$_pr->insert(array('idPage' => $idPage, 'idReferral' => $id));
				} catch(Exception $e) {
					// already in db
				}
			}
		}
	}
}