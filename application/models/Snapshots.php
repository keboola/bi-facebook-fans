<?php
/**
 * Snapshots
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-09-15
 */

class Model_Snapshots extends Zend_Db_Table
{
	protected $_name = 'fbi_snapshots';

	public function add($date)
	{
		$d = $this->fetchRow(array('date=?' => $date));
		if ($d) {
			return $d->id;
		} else {
			return $this->insert(array('date' => $date));
		}
	}
}