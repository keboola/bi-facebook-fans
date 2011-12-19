<?php

/**
 * Google Analytics Connector Setup Form
 *
 * @author Miroslav Čillík <miro@keboola.com>
 */
class Form_GoogleAnalyticsSetup extends Zend_Form
{

	public function setProfiles($profiles)
	{
		$session = new Zend_Session_Namespace();		

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

		$table = new Model_Profile();
		$res = $table->getAdapter()->fetchAll(
			'SELECT * FROM ga_profiles WHERE gaProfileId IN (' . implode(",", array_keys($options)) . ')');		

		$values = array();
		foreach($res as $row) {
			$values[] = $row['gaProfileId'];
		}
		$this->getElement('profiles')->setValue($values);
		
	}

	public function init()
	{
		parent::init();

		$this->setMethod('POST');

		$this->setAttrib('id', 'userForm');

		$this->addElement('text', 'email', array(
			'required'	=> true,
			'label'		=> 'Email (For GoodData project)',
			'validators'	=> array('NotEmpty', 'EmailAddress')
		));

		$this->addElement('multiCheckbox', 'profiles', array(
			'label'		=> 'Profiles to import',
			'required'	=> true
		));
		$this->getElement('profiles')->setRegisterInArrayValidator(false);

		$this->addElement('submit', 'submit', array(
			'ignore'	=> true,
			'label'		=> 'Send'
		));

		$this->addElement('hidden', 'accountTitle');

	}
}
?>
