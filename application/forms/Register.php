<?php
/**
 *
 * @author Jakub Matejka <jakub@keboola.com>
 */
class Form_Register extends App_Form
{
	public function init()
	{
		parent::init();
		$this->setAttrib('class', 'form');

		$this->addElement('text', 'email', array(
			'required'	=> true,
			'label'		=> 'form.register.email',
			'validators' => array('NotEmpty', 'EmailAddress', new App_Validate_UserExists())
		));

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

		$this->addElement('Captcha', 'captcha', array(
			'label' => 'form.register.captcha',
			'description' => 'form.register.captcha.description',
			'required' => true,
			'captcha' => array(
				'captcha' => 'image',
				'name' => 'foo',
				'wordLen' => 5,
				'font' => ROOT_PATH . '/public/captcha/lido.ttf',
				'fontSize' => 24,
				'height' => 50,
				'width' => 200,
				'imgDir' => ROOT_PATH . '/public/captcha',
				'imgUrl' => '/captcha/',
				'LineNoiseLevel' => 0
			)
		));

		$this->addElement('image', 'submit', array(
			'src'		=> '/img/button-register.png',
			'ignore'	=> true,
			'label'		=> 'form.register.save'
		));

		$this->addDisplayGroup(array('email', 'password', 'passwordConfirm', 'captcha'), 'basic');
		$this->addDisplayGroup(array('submit'), 'buttons');
	}
}
?>
