<?php
/**
 *
 * @author Jakub Matejka <jakub@keboola.com>
 */
class Form_Login extends Zend_Form
{
	public $_textDecorators = array(
		array('ViewHelper'),
		array('Label', array('escape' => false)),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formElement inputText')),
		array('Errors'),
	);

	public function loadDefaultDecorators()
	{
		$this->setDecorators(array(
			array('FormElements'),
			array('HtmlTag', array('tag' => '<div>')),
			array('Form')
		));

		$this->setDisplayGroupDecorators(array(
			array('FormElements'),
			array('Description', array('placement' => 'prepend')),
			array('Fieldset')
		));
	}

	public function init()
	{
		parent::init();
		$this->setAttrib('class', 'form');

		$this->addElement('text', 'email', array(
			'required'		=> true,
			'label'			=> 'form.login.email',
			'validators'	=> array('NotEmpty', 'EmailAddress'),
			'decorators'	=> $this->_textDecorators
		));

		$this->addElement('password', 'password', array(
			'required'		=> true,
			'label'			=> 'form.login.password',
			'validators'	=> array('NotEmpty'),
			'decorators'	=> $this->_textDecorators
		));

		$this->addElement('hash', 'csrf', array(
			'decorators'	=> array(
				array('ViewHelper'),
				array('Errors')
			)
		));

		$this->addElement('image', 'submit', array(
			'src'			=> '/img/button-login.png',
			'ignore'		=> true,
			'label'			=> 'form.login.login',
			'class'			=> 'loginButton',
			'decorators'	=> array(
				array('ViewHelper'),
				array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formElement noLabel'))
			)
		));

		$this->addDisplayGroup(array('email', 'password', 'csrf', 'submit'), 'basic');
	}
}
?>
