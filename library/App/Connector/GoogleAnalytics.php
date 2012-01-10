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
	private $_usersToAccountsTable;

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
		$this->_usersToAccountsTable = $this->_dbPrefix.'rUsersAccounts';
		$this->_accountsTable = $this->_dbPrefix.'accounts';
		$this->_usersTable = $this->_dbPrefix.'users';
	}


	/**
	 * @param $idUser - User id
	 * @param $idFB
	 * @param $accounts
	 */
	public function addProfilesToAccount($idUser, $googleId, $profiles)
	{
		$session = new Zend_Session_Namespace("GoogleAnalyticsForm");

		//@TODO: M:N users - accounts

		//Create user
		$userExists = $this->_db->fetchOne('SELECT COUNT(*) FROM '.$this->_usersTable.' WHERE id = ?', $idUser);

		if (!$userExists) {
			$this->_db->insert($this->_usersTable, array(
				'id'	=> $idUser
			));
		}

		$dbAccountId = $this->_db->fetchOne('SELECT id FROM '.$this->_accountsTable.' WHERE googleId = ?', $googleId);

		//@FIXME: error message when user doesnt have refresh token?
		$data['gaAccessToken'] = $session->oauthToken;
		if (isset($session->refreshToken)) {
			$data['gaRefreshToken'] = $session->refreshToken;
		}

		if(!$dbAccountId) {
			$data['googleId'] = $googleId;		
			$this->_db->insert($this->_accountsTable, $data);
			$dbAccountId = $this->_db->lastInsertId($this->_accountsTable);
		} else {
			$this->_db->update($this->_accountsTable, $data, array('id = ?' => $dbAccountId));
		}

		// Relation exists?
		$usersToAccountsId = $this->_db->fetchOne('SELECT id FROM '.$this->_usersToAccountsTable.' WHERE userId = ? AND accountId = ?', array($idUser, $dbAccountId));

		if (!$usersToAccountsId) {
			$this->_db->insert($this->_usersToAccountsTable, array(
				'userId'	=> $idUser,
				'accountId'	=> $dbAccountId
			));
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

			// Relation exists?
			$accountsToProfiles = $this->_db->fetchOne('SELECT id FROM '.$this->_accountsToProfilesTable.' WHERE profileId = ? AND accountId = ?', array($dbProfileId, $dbAccountId));
			if (!$accountsToProfiles) {				
				$this->_db->insert($this->_accountsToProfilesTable, array(
					'accountId'	=> $dbAccountId,
					'profileId'	=> $dbProfileId
				));
			}

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
	public function getProfiles($idUser)
	{
		return $this->_db->fetchPairs('
			SELECT p.gaProfileId, p.gaName
			FROM '.$this->_profilesTable.' p
			LEFT JOIN '.$this->_accountsToProfilesTable.' atp ON (p.id = atp.profileId)
			LEFT JOIN '.$this->_usersToAccountsTable.' uta ON (atp.accountId = uta.accountId)				
			WHERE uta.userId = ?',
			array($idUser));
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