<?php
/**
 *
 * @author Jakub Matejka <jakub@keboola.com>
 */
class Form_AddPage extends App_Form
{
	public function init()
	{
		parent::init();

		$this->addElement('text', 'name', array(
			'required'	=> true,
			'label'		=> 'Page Name'
		));

		$this->addElement('text', 'idPage', array(
			'required'	=> true,
			'label'		=> 'Page Id'
		));

		$this->addElement('hidden', 'fbToken');

		$this->addElement('submit', 'submit', array(
			'ignore'	=> true,
			'label'		=> 'Send'
		));

		$this->addDisplayGroup(array('name', 'idPage', 'fbToken', 'submit'), 'basic');
	}
}
?>
