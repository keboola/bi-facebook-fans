<?php
/**
 * @author Miroslav Cillik <miro@keboola.com>
 */

class Model_PagesUsers extends App_Db_Table
{
	protected $_name = 'rPagesUsers';
	protected $_referenceMap = array(
		'Users'	=> array(
			'columns'		=> array('idUser'),
			'refTableClass'	=> 'Model_Users',
			'refColumns'	=> array('id')
		),
		'Pages'	=> array(
			'columns'		=> array('idPage'),
			'refTableClass'	=> 'Model_Pages',
			'refColumns'	=> array('id')
		)
	);

	public function save(array $data)
	{
		$row = $this->fetchRow(array(
			'idUser=?' => $data['idUser'],
			'idPage=?' => $data['idPage']
		));

		if ($row) {
			$row->setFromArray($data);
			$row->save();
			return $row['id'];
		}

		return $this->insert($data);
	}

}

