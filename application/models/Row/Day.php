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
		$_uc->delete(array('idDay=?' => $this->id));

		foreach ($data as $k => $v) {
			$_uc->insert(array('idDay' => $this->id, 'country' => $k, 'views' => $v));
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
		$_dr->delete(array('idDay=?' => $this->id));

		foreach ($internal as $k => $v) {
			$r = $_r->fetchRow(array('name=?' => $k));
			if (!$r) {
				$id = $_r->insert(array('name' => $k, 'type' => 'internal'));
			} else {
				$id = $r->id;
			}
			$_dr->insert(array('idDay' => $this->id, 'idReferral' => $id, 'views' => $v));
		}
		foreach ($external as $k => $v) {
			$r = $_r->fetchRow(array('name=?' => $k));
			if (!$r) {
				$id = $_r->insert(array('name' => $k, 'type' => 'external'));
			} else {
				$id = $r->id;
			}
			$_dr->insert(array('idDay' => $this->id, 'idReferral' => $id, 'views' => $v));
		}
	}
}