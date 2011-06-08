<?php
/**
 * Dayily page stats
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-08
 */

class Model_Days extends Zend_Db_Table
{
	protected $_name = 'fbi_days';

	/**
	 * @param  $date
	 * @param  $value
	 * @return void
	 */
	public function add($date, $visits, $likes)
	{
		$this->getAdapter()->query('REPLACE INTO fbi_days SET date=?, visits=?, likes=?', array($date, $visits, $likes));
	}
}