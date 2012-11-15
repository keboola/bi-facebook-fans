<?php
/**
 * @author Miro Cillik <miro@keboola.com>
 * @date 2011-10-13
 */

class Model_Objects extends App_Db_Table
{
	protected $_name = 'objects';

	public function save(array $data)
	{
		$data['timestamp'] = date('Y-m-d H:i:s');

		if (isset($data['id'])) {
			$this->update($data, array('id=?' => $data['id']));
			return $data['id'];
		}
		if (isset($data['idFB'])) {
			$row = $this->fetchRow(array('idFB=?' => $data['idFB']));

			if ($row) {
				$row->setFromArray($data);
				$row->save();
				return $row->id;
			}
		}

		return $this->insert($data);
	}

}

