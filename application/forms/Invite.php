<?php
/**
 *
 * @author Jakub Matejka <jakub@keboola.com>
 */
class Form_Invite extends App_Form
{
	public function init()
	{
		parent::init();
		$this->setAttrib('class', 'inviteForm');
		$this->setName('inviteForm');

		$this->addElement('select', 'role', array(
			'label'			=> 'form.invite.role',
			'multiOptions'	=> array(
				'editor'			=> 'form.invite.editor',
				'dashboard only'	=> 'form.invite.dashboardOnly',
			)
		));

		$this->addElement('textarea', 'text', array(
			'label'			=> 'form.invite.text'
		));

		$this->addElement('text', 'email', array(
			'required'		=> true,
			'label'			=> 'form.invite.email',
			'validators'	=> array('NotEmpty', 'EmailAddress')
		));

		$this->addElement('hidden', 'job', array(
			'value'		=> 'invite'
		));

		$this->addElement('image', 'invite', array(
			'src'		=> '/img/button-invite-inactive.png',
			'disabled'	=> 'disabled',
			'ignore'	=> true,
			'label'		=> '&nbsp;'
		));

		$this->addDisplayGroup(array('role'), 'roleFIeld');
		$this->addDisplayGroup(array('text'), 'textField');
		$this->addDisplayGroup(array('email', 'invite'), 'buttonField');

	}

	public function loadDefaultDecorators()
	{
		parent::loadDefaultDecorators();
		$this->getElement('invite')->setDecorators(array(
			array('ViewHelper'),
			array('Label', array('escape' => false)),
			array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formElement')),
		));
	}
}
