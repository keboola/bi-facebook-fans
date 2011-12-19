<?php
/**
 * GoodData Service class
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-10-19
 *
 */

class App_GoodDataService
{
	/**
	 * @var App_GoodData
	 */
	private $_gd;


	public function __construct($username, $password)
	{
		$this->_gd = new App_GoodData($username, $password);
	}


	/**
	 * Clone project from template
	 * @param $name
	 * @param $templateUri
	 * @return bool|string
	 */
	public function createProject($name, $templateUri=null)
	{
		$output = $this->_gd->call('CreateProject(name="'.$name.'" templateUri="'.$templateUri.'");');

		if(!strpos($output, 'ERROR')) {
			$start = strpos($output, 'id = \'');
			$end = strpos($output, '\' created.');
			return trim(substr($output, $start+6, $end-$start-6));
		} else {
			return FALSE;
		}
	}

	/**
	 * @param $idProject
	 * @param $email
	 * @param string $role
	 * @param null $text
	 * @return bool
	 */
	public function inviteUser($idProject, $email, $role="editor", $text=null)
	{
		$output = $this->_gd->call(
			'OpenProject(id="'.$idProject.'"); ' .
			'InviteUser(email="'.$email.'", msg="'.$text.'", role="'.$role.'");'
		);

		/*
		 //@TODO Get users GD id
		 $uriFile = ROOT_PATH.'/tmp/users-uri-'.$idProject.'.txt';
		$emailFile = ROOT_PATH.'/tmp/users-email-'.$idProject.'.txt';

		shell_exec('rm -rf '.$uriFile);
		shell_exec('rm -rf '.$emailFile);
		$output = $this->_gd->call(
			'OpenProject(id="'.$idProject.'"); ' .
			'GetProjectUsers(usersFile="'.$emailFile.'", field="email", activeOnly="false");'.
				'GetProjectUsers(usersFile="'.$uriFile.'", field="uri", activeOnly="false");'
		);

		$emails = file_get_contents($emailFile);
		$uris = file_get_contents($uriFile);

		Zend_Debug::dump($emails);Zend_Debug::dump($uris);die();*/

		if(!strpos($output, 'ERROR')) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function disableUser($idProject)
	{

		$output = $this->_gd->call(
			'OpenProject(id="'.$idProject.'"); ' .
			'GetProjectUsers(usersFile="'.ROOT_PATH.'/tmp/users-'.$idProject.'.txt", field="email", activeOnly="false");'
		);
		echo $output;
	}

	/**
	 * @TODO
	 * @param $id
	 * @param $templatePID
	 * @param $targetPID
	 */
	public function copyMetaData($id, $templatePID, $targetPID)
	{
		/*$templateProject = new App_GoodDataProject($templatePID,  $this->gd());
		echo $output = $templateProject->call('ExportMetadataObjects(tokenFile="token.txt", objectIDs="'.$id.'");');
		if (strpos($output, 'Import token is ')) {
			$targetProject = new App_GoodDataProject($targetPID,  $this->gd());
			echo $targetProject->call('ImportMetadataObjects(tokenFile="token.txt", overwrite="true", updateLDM="false");');
		}*/
	}

	/**
	 * @return App_GoodData
	 */
	public function gd()
	{
		return $this->_gd;
	}

}