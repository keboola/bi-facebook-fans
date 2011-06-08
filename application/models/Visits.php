<?php
/**
 * Visits
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-08
 */

class Model_Visits extends Zend_Db_Table
{
	protected $_name = 'fbi_visits';

	/**
	 * @param  $date
	 * @param  $value
	 * @return void
	 */
	public function add($date, $value)
	{
		$this->getAdapter()->query('REPLACE INTO fbi_visits SET date=?, value=?', array($date, $value));
	}
}