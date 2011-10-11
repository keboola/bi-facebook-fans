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

		$this->addElement('text', 'email', array(
			'required'	=> true,
			'label'		=> 'Email',
			'validators' => array('NotEmpty', 'EmailAddress')
		));

		$this->addElement('multiCheckbox', 'pages', array(
			'required'	=> true,
			'label'		=> 'Pages',
			'registerInArrayValidator' => false,
			'validators' => array('NotEmpty')
		));

		$this->addElement('text', 'idProject', array(
			'label'		=> 'GoodData Project Id'
		));

		$this->addElement('hidden', 'fbToken');
		$this->addElement('hidden', 'idUser');

		$this->addElement('submit', 'submit', array(
			'ignore'	=> true,
			'label'		=> 'Send'
		));

		$this->addDisplayGroup(array('email', 'pages', 'idProject', 'fbToken', 'idUser', 'email', 'submit'), 'basic');
	}
}
?>
