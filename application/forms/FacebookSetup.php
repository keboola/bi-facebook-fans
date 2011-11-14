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

		$this->addElement('image', 'submit', array(
			'src'		=> '/img/button-save-inactive.png',
			'ignore'	=> true,
			'label'		=> 'form.facebookSetup.order'
		));

		$this->addDisplayGroup(array('pages'), 'basic');
		$this->addDisplayGroup(array('submit'), 'buttons');
	}
}
?>
