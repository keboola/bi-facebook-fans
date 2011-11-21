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
	public function createProject($name, $templateUri)
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
	 * @return mixed
	 */
	public function inviteUser($idProject, $email)
	{
		$output = $this->_gd->call(
			'OpenProject(id="'.$idProject.'"); ' .
			'InviteUser(email="'.$email.'", msg="", role="editor");'
		);

		if(!strpos($output, 'ERROR')) {
			return TRUE;
		} else {
			return FALSE;
		}
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