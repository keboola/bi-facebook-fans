<?php

/**
 * Description of Friends
 *
 * @author Miroslav Čillík <miro@keboola.com>
 */
class Model_Friends extends App_Db_Table {

    protected $_name = 'friends';

	public function save(array $data)
	{
		$data['timestamp'] = date('Y-m-d H:i:s');
		
		if (isset($data['id'])) {
			$this->update($data, array('id=?' => $data['id']));
			return $data['id'];
		}
		if (isset($data['idFB']) && isset($data['idUser'])) {
			$row = $this->fetchRow(array('idFB=?' => $data['idFB'], 'idUser' => $data['idUser']));

			if ($row) {
				$row->setFromArray($data);
				$row->save();
				return $row->id;
			}
		}

		return $this->insert($data);
	}
}
?>
