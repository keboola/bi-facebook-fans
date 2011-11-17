<?php
/**
 *
 * @author Jakub Matejka <jakub@keboola.com>
 */
class Form_LostPassword extends App_Form
{
	public function init()
	{
		parent::init();
		$this->setAttrib('class', 'form');

		$this->addElement('text', 'email', array(
			'required'		=> true,
			'label'			=> 'form.login.email',
			'validators'	=> array('NotEmpty', 'EmailAddress')
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
			'src'			=> '/img/button-send.png',
			'ignore'		=> true,
			'label'			=> 'form.login.login',
			'class'			=> 'loginButton',
			'decorators'	=> array(
				array('ViewHelper'),
				array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formElement noLabel'))
			)
		));

		$this->addDisplayGroup(array('email', 'captcha'), 'basic');
		$this->addDisplayGroup(array('submit'), 'buttons');
	}
}
