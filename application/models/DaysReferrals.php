<?php
/**
 * Dayily active users referrals
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-08
 */

class Model_DaysReferrals extends Zend_Db_Table
{
	protected $_name = 'fbi_rDaysReferrals';

	/**
	 * Get just rows relevant for given page
	 * @param $idPage
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function fetchForPage($idPage)
	{
		$select = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
			->setIntegrityCheck(false)
			->where('idPage=?', $idPage)
			->joinLeft('fbi_days', 'fbi_days.id = '.$this->_name.'.idDay');
		return $this->fetchAll($select);

	}
}