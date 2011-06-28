<?php
/**
 * Page row
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-08
 */

class Model_Row_Page extends Zend_Db_Table_Row_Abstract
{

	/**
	 * @param array $data
	 */
	public function addAge($data, $date)
	{
		$_a = new Model_Age();
		$_pa = new Model_PagesAge();

		foreach ($data as $k => $v) {
			$r = $_a->fetchRow(array('name=?' => $k));
			if (!$r) {
				$id = $_a->insert(array('name' => $k));
			} else {
				$id = $r->id;
			}
			$pa = $_pa->fetchRow(array('idPage=?' => $this->id, 'idAge=?' => $id));
			if (!$pa) {
				$_pa->insert(array('idPage' => $this->id, 'idAge' => $id, 'likes' => $v, 'date' => $date));
			} else {
				if ($pa->date < $date) {
					$pa->likes = $v;
					$pa->date = $date;
					$pa->save();
				}
			}
		}
	}

	/**
	 * @param array $data
	 */
	public function addCities($data, $date)
	{
		$_c = new Model_Cities();
		$_pc = new Model_PagesCities();

		foreach ($data as $k => $v) {
			$r = $_c->fetchRow(array('name=?' => $k));
			if (!$r) {
				$id = $_c->insert(array('name' => $k));
			} else {
				$id = $r->id;
			}
			$pc = $_pc->fetchRow(array('idPage=?' => $this->id, 'idCity=?' => $id));
			if (!$pc) {
				$_pc->insert(array('idPage' => $this->id, 'idCity' => $id, 'likes' => $v, 'date' => $date));
			} else {
				if ($pc->date < $date) {
					$pc->likes = $v;
					$pc->date = $date;
					$pc->save();
				}
			}
		}
	}

	/**
	 * @param array $data
	 */
	public function addCountries($data, $date)
	{
		$_c = new Model_PagesCountries();

		foreach ($data as $k => $v) {
			$c = $_c->fetchRow(array('idPage=?' => $this->id, 'country=?' => $k));
			if (!$c) {
				$_c->insert(array('idPage' => $this->id, 'country' => $k, 'likes' => $v, 'date' => $date));
			} else {
				if ($c->date < $date) {
					$c->likes = $v;
					$c->date = $date;
					$c->save();
				}
			}
		}
	}
}