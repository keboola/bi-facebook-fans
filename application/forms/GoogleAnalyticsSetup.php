<?php

/**
 * Google Analytics Connector Setup Form
 *
 * @author Miroslav Čillík <miro@keboola.com>
 */
class Form_GoogleAnalyticsSetup extends App_Form
{

	public function setProfiles($profiles)
	{
		$session = new Zend_Session_Namespace("GoogleAnalyticsForm");

		$options = array();
		foreach($profiles as $acc => $wps) {
			foreach($wps as $wp) {
				foreach($wp as $p) {
					$session->profiles[$p['id']] = array(
						'name'		=> $acc . '-' . $p['name'],
						'profileId'	=> $p['id'],
						'accountId'	=> $p['accountId'],
						'webPropertyId'	=> $p['webPropertyId']
					);

					$options[$p['id']] = $acc . '-' . $p['name'];
				}
			}
		}
		$this->getElement('profiles')->setMultiOptions($options);
	}

	public function init()
	{
		parent::init();

		$this->setMethod('POST');

		$this->setAttrib('class', 'googleAnalyticsForm');
		$this->setName('googleAnalyticsForm');

		$this->addElement('multiCheckbox', 'profiles', array(
			'label'		=> 'Profiles to import',
			'required'	=> true,
			'registerInArrayValidator' => false,
			'validators' => array('NotEmpty')
		));

		$this->addElement('hidden', 'job', array(
			'value'		=> 'register'
		));

		/**
		 * 
		$this->addElement('submit', 'submit', array(
			'ignore'	=> true,
			'label'		=> 'Send'
		));
		 * 
		 */

		$this->addElement('hidden', 'accountTitle');

		$this->addElement('image', 'submit', array(
			'src'		=> '/img/button-save-inactive.png',
			'ignore'	=> true,
			'label'		=> 'form.googleAnalyticsSetup.save'
		));		

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
