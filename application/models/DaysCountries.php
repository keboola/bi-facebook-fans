<?php
/**
 * Days to Countries
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-08
 */

class Model_DaysCountries extends Zend_Db_Table
{
	protected $_name = 'fbi_daysCountries';

	public function fetchForPage($idPage)
	{
		$select = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART)->setIntegrityCheck(false)
				->where('idPage=?', $idPage)
				->joinLeft('fbi_days', 'fbi_days.id = '.$this->_name.'.idDay');
		return $this->fetchAll($select);
	}
}