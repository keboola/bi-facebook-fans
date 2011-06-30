<?php
/**
 * Cities
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-28
 */

class Model_Cities extends Zend_Db_Table
{
	protected $_name = 'fbi_cities';

	/**
	 * Get just rows relevant for given page
	 * @param $idPage
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function fetchForPage($idPage)
	{
		$select = $this->select()->setIntegrityCheck(false)
			->from($this, array('id', 'name'))
			->joinLeft('fbi_rPagesCities', 'id = fbi_rPagesCities.idCity', null)
			->where('idPage=?', $idPage);
		return $this->fetchAll($select);
	}
}