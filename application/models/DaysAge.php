<?php
/**
 * Days to Age
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-28
 */

class Model_DaysAge extends Zend_Db_Table
{
	protected $_name = 'fbi_rDaysAge';

	/**
	 * Get just rows relevant for given page
	 * @param $idPage
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function fetchForPage($idPage)
	{
		$select = $this->select()->setIntegrityCheck(false)
			->from($this, array('idDay', 'idAge', 'views'))
			->joinLeft('fbi_days', 'fbi_days.id = '.$this->_name.'.idDay', null)
			->where('idPage=?', $idPage);
		return $this->fetchAll($select);

	}
}