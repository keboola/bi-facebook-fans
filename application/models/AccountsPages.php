<?php
/**
 * @author Miroslav Cillik <miro@keboola.com>
 */

class Model_AccountsPages extends App_Db_Table
{
	protected $_name = 'rAccountsPages';
	protected $_referenceMap = array(
		'Accounts'	=> array(
			'columns'		=> array('idAccount'),
			'refTableClass'	=> 'Model_Accounts',
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
			'idAccount=?' => $data['idAccount'],
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

