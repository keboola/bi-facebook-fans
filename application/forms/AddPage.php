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
			'label'		=> 'Page Name',
			'validators' => array('NotEmpty')
		));

		$this->addElement('text', 'idPage', array(
			'required'	=> true,
			'label'		=> 'Facebook Page Id',
			'validators' => array('NotEmpty')
		));

		$this->addElement('text', 'idProject', array(
			'required'	=> true,
			'label'		=> 'GoodData Project Id',
			'validators' => array('NotEmpty')
		));

		$this->addElement('hidden', 'fbToken');
		$this->addElement('hidden', 'idUser');
		$this->addElement('hidden', 'email');

		$this->addElement('submit', 'submit', array(
			'ignore'	=> true,
			'label'		=> 'Send'
		));

		$this->addDisplayGroup(array('name', 'idPage', 'idProject', 'fbToken', 'idUser', 'email', 'submit'), 'basic');
	}
}
?>
