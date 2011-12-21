<?php

/**
 *
 * @author Miro Cillik <miro@keboola.com>
 */
class App_Connector_GoogleAnalytics extends App_Connector
{
	/**
	 * @var string
	 */
	private $_profilesTable;

	/**
	 * @var string
	 */
	private $_accountsToProfilesTable;

	/**
	 * @var string
	 */
	private $_accountsTable;
	
	/**
	 * @var string
	 */
	private $_usersTable;



	public function __construct()
	{
		parent::__construct('google-analytics');

		$this->_profilesTable = $this->_dbPrefix.'profiles';
		$this->_accountsToProfilesTable = $this->_dbPrefix.'rAccountsProfiles';
		$this->_accountsTable = $this->_dbPrefix.'accounts';
		$this->_usersTable = $this->_dbPrefix.'users';
	}


	/**
	 * @param $idUser - User id
	 * @param $idFB
	 * @param $accounts
	 */
	public function addProfilesToAccount($idUser, $profiles)
	{
		$session = new Zend_Session_Namespace("GoogleAnalyticsForm");

		//Create user
		$userExists = $this->_db->fetchOne('SELECT COUNT(*) FROM '.$this->_usersTable.' WHERE id = ?', $idUser);

		if (!$userExists) {
			$this->_db->insert($this->_usersTable, array(
				'id'	=> $idUser
			));
		}

		$dbAccountId = $this->_db->fetchOne('SELECT id FROM '.$this->_accountsTable.' WHERE googleId = ?', $sesssion->googleUserId);
		if(!$dbAccountId) {
			$data = array(
				'googleId'		=> $session->googleUserId,
				'userId'		=> $idUser,
				'gaAccessToken'	=> $session->oauthToken
			);

			//@TODO: check if user has refresh token!
			if (isset($session->refreshToken)) {
				$data['gaRefreshToken'] = $session->refreshToken;
			}
			$this->_db->insert($this->_accountsTable, $data);
			$dbAccountId = $this->_db->lastInsertId($this->_accountsTable);
		}

		foreach($profiles as $profileId) {

			$dbProfileId = $this->_db->fetchOne('SELECT id FROM '.$this->_profilesTable.' WHERE gaProfileId=?', $profileId);

			if (!$dbProfileId) {
				$this->_db->insert($this->_profilesTable, array(
					'gaName'		=> $session->profiles[$profileId]['name'],
					'gaAccountId'	=> $session->profiles[$profileId]['accountId'],
					'gaProfileId'	=> $profileId,
					'gaWebPropertyId'	=> $session->profiles[$profileId]['webPropertyId']
				));
				$dbProfileId = $this->_db->lastInsertId($this->_profilesTable);
			}

			// @FIXME userId -> accountId - change in db too
			$this->_db->insert($this->_accountsToProfilesTable, array(
				'accountId'	=> $dbAccountId,
				'profileId'	=> $dbProfileId
			));

		}
		

	}

	/**
	 * @param $idUser
	 * @param $idFBAccount
	 * @return bool
	 */
	public function isKnownUserToAccount($idUser, $idFBAccount)
	{
		$userToAccount = $this->_db->fetchOne('
			SELECT COUNT(*)
			FROM '.$this->_usersToAccountsTable.' ota
			LEFT JOIN '.$this->_accountsTable.' a ON (a.id = ota.idAccount)
			WHERE a.idFB=? AND ota.idUser=?',
			array($idFBAccount, $idUser));
		return $userToAccount ? TRUE : FALSE;
	}

	/**
	 * @param $idAccount
	 * @return array
	 */
	public function getGoogleUser($googleId)
	{
		return $this->_db->fetchRow('
			SELECT *
			FROM '.$this->_accountsTable.'
			WHERE googleId = ?',
			array($googleId));
	}

	/**
	 * @param $idUser
	 * @return array
	 */
	public function getProfiles($googleId)
	{
		return $this->_db->fetchPairs('
			SELECT p.gaProfileId, p.gaName
			FROM '.$this->_profilesTable.' p
			LEFT JOIN '.$this->_accountsToProfilesTable.' utp ON (p.id = utp.accountId)
			LEFT JOIN '.$this->_accountsTable.' a ON (a.id = utp.accountId)
			WHERE a.googleId = ?',
			array($googleId));
	}

	/**
	 * @param $idUser
	 */
	public function userHasProject($idUser)
	{
		$this->_db->query('
			UPDATE '.$this->_usersTable.'
			SET hasProject = 1
			WHERE id = ?',
			array($idUser));
	}

	/**
	 * @param $idUser
	 * @param $idAccount
	 * @param $token
	 */
	public function saveNewToken($idUser, $idAccount, $token)
	{
		$this->_db->query('
			UPDATE '.$this->_usersToAccountsTable.'
			SET oauthToken = ?, isValid = 1
			WHERE idUser = ?
			AND idAccount = ?',
			array($token, $idUser, $idAccount));

		$this->_db->query('
			UPDATE '.$this->_accountsTable.'
			SET import = 1
			WHERE id = ?',
			array($idAccount));
	}

}