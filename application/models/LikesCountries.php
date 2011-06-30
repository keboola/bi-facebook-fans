<?php
/**
 * Pages to Countries
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-28
 */

class Model_LikesCountries extends Zend_Db_Table
{
	protected $_name = 'fbi_likesCountries';

	/**
	 * Get just rows relevant for given page
	 * @param $idPage
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function fetchForPage($idPage)
	{
		$select = $this->select()->setIntegrityCheck(false)
			->from($this, array('id', 'idLike', 'country', 'likes'))
			->joinLeft('fbi_likes', 'fbi_likes.id = '.$this->_name.'.idLike', null)
			->where('idPage=?', $idPage);
		return $this->fetchAll($select);
	}
}