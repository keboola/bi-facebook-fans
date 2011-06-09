<?php
/**
 * Dayily page stats
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-08
 */

class Model_Days extends Zend_Db_Table
{
	protected $_name = 'fbi_days';
	protected $_rowClass = 'Model_Row_Day';

	/**
	 * @param  $data
	 * @return int
	 */
	public function add($data)
	{
		$r = $this->fetchRow(array('date=?' => $data['date']));
		if ($r) {
			$r->setFromArray($data);
			$r->save();
			return $r->id;
		} else {
			return $this->insert($data);
		}
	}
}