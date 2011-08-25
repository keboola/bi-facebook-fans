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
		$data['viewsTotal'] = $this->getViewsTotal($data['idPage']);
		if (isset($data['views'])) {
			$data['viewsTotal'] += $data['views'];
		}

		$r = $this->fetchRow(array('idPage=?' => $data['idPage'], 'date=?' => $data['date']));
		if ($r) {
			$r->setFromArray($data);
			$r->save();
			$id = $r->id;
		} else {
			$id = $this->insert($data);
		}

		return $this->fetchRow(array('id=?' => $id));
	}

	public function getViewsTotal($idPage)
	{
		return $this->getAdapter()->fetchOne('SELECT viewsTotal FROM '.$this->_name.' WHERE idPage = ? ORDER BY date DESC', $idPage);
	}
}