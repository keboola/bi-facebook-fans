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
	public function addUserCountries($data)
	{
		$_uc = new Model_DaysUserCountries();

		foreach ($data as $k => $v) {
			if (!$_uc->fetchRow(array('idDay=?' => $this->id, 'country=?' => $k))) {
				$_uc->insert(array('idDay' => $this->id, 'country' => $k, 'views' => $v));
			}
		}
	}

	/**
	 * @param array $internal
	 * @param array $external
	 */
	public function addReferrals($internal, $external)
	{
		$_r = new Model_Referrals();
		$_dr = new Model_DaysReferrals();

		foreach ($internal as $k => $v) {
			$r = $_r->fetchRow(array('name=?' => $k));
			if (!$r) {
				$id = $_r->insert(array('name' => $k, 'type' => 'internal'));
			} else {
				$id = $r->id;
			}
			if (!$_dr->fetchRow(array('idDay=?' => $this->id, 'idReferral=?' => $id))) {
				$_dr->insert(array('idDay' => $this->id, 'idReferral' => $id, 'views' => $v));
			}
		}
		foreach ($external as $k => $v) {
			$r = $_r->fetchRow(array('name=?' => $k));
			if (!$r) {
				$id = $_r->insert(array('name' => $k, 'type' => 'external'));
			} else {
				$id = $r->id;
			}
			if (!$_dr->fetchRow(array('idDay=?' => $this->id, 'idReferral=?' => $id))) {
				$_dr->insert(array('idDay' => $this->id, 'idReferral' => $id, 'views' => $v));
			}
		}
	}

	public function totalViews()
	{
		return $this->getTable()->getAdapter()->fetchOne('SELECT COUNT(views) FROM fbi_days WHERE date <= ?', $this->date);
	}
}