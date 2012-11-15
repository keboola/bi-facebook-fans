<?php
/**
 * @author Miroslav Cillik <miro@keboola.com>
 */

class Model_StatusMessagesObjects extends App_Db_Table
{
	protected $_name = 'rStatusMessagesObjects';
	protected $_referenceMap = array(
		'StatusMessages'	=> array(
			'columns'		=> array('idStatusMessage'),
			'refTableClass'	=> 'Model_StatusMessages',
			'refColumns'	=> array('id')
		),
		'Objects'	=> array(
			'columns'		=> array('idObject'),
			'refTableClass'	=> 'Model_Objects',
			'refColumns'	=> array('id')
		)
	);

	public function save(array $data)
	{
		$row = $this->fetchRow(array(
			'idStatusMessage=?' => $data['idStatusMessage'],
			'idObject=?' => $data['idObject']
		));

		if ($row) {
			$row->setFromArray($data);
			$row->save();
			return $row['id'];
		}

		return $this->insert($data);
	}

}

