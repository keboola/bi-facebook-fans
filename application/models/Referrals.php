<?php
/**
 * Dayily active users referrals
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-08
 */

class Model_Referrals extends Zend_Db_Table
{
	protected $_name = 'fbi_referrals';

	/**
	 * Get just rows relevant for given page
	 * @param $idPage
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function fetchForPage($idPage)
	{
		$select = $this->select()->setIntegrityCheck(false)
			->from($this, array('id', 'name', 'type'))
			->joinLeft('fbi_rPagesReferrals', 'id = fbi_rPagesReferrals.idReferral', null)
			->where('idPage=?', $idPage);
		return $this->fetchAll($select);
	}
}