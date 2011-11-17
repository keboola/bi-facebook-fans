<?php
/**
 *
 * @author Jakub Matejka <jakub@keboola.com>
 */
class Form_NewPassword extends App_Form
{
	public function init()
	{
		parent::init();
		$this->setAttrib('class', 'form');

		$this->addElement('password', 'password', array(
			'required'	=> true,
			'label'		=> 'form.register.password',
			'validators' => array('NotEmpty', new App_Validate_PasswordConfirmation())
		));

		$this->addElement('password', 'passwordConfirm', array(
			'required'	=> true,
			'label'		=> 'form.register.passwordConfirm',
			'validators' => array('NotEmpty')
		));

		$this->addElement('image', 'submit', array(
			'src'			=> '/img/button-send.png',
			'ignore'		=> true,
			'label'			=> 'form.login.login',
			'class'			=> 'loginButton',
			'decorators'	=> array(
				array('ViewHelper'),
				array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formElement noLabel'))
			)
		));

		$this->addDisplayGroup(array('password', 'passwordConfirm'), 'basic');
		$this->addDisplayGroup(array('submit'), 'buttons');
	}
}
