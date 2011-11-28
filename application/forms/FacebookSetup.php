<?php
/**
 *
 * @author Jakub Matejka <jakub@keboola.com>
 */
class Form_FacebookSetup extends App_Form
{
	public function init()
	{
		parent::init();
		$this->setAttrib('class', 'facebookForm');
		$this->setName('facebookForm');

		$this->addElement('multiCheckbox', 'pages', array(
			'required'	=> true,
			'label'		=> 'form.facebookSetup.pages',
			'registerInArrayValidator' => false,
			'validators' => array('NotEmpty')
		));

		$this->addElement('hidden', 'job', array(
			'value'		=> 'register'
		));

		$this->addElement('image', 'submit', array(
			'src'		=> '/img/button-save-inactive.png',
			'ignore'	=> true,
			'label'		=> 'form.facebookSetup.save'
		));

		$this->addDisplayGroup(array('pages'), 'basic');
		$this->addDisplayGroup(array('submit'), 'buttons');
	}

	public function loadDefaultDecorators()
	{
		parent::loadDefaultDecorators();

		$this->getElement('submit')->setDecorators(array(
			array('viewScript', array('viewScript' => 'helpers/facebookButtons.phtml'))
		));
	}
}
?>
