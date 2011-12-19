<?
/**
 * @TODO Upravit pro konkrétní konektor, převzato z Facebooku!
 * @author
 * @date
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
	private $_usersToProfilesTable;

	/**
	 * @var string
	 */
	private $_usersTable;



	public function __construct()
	{
		parent::__construct('google-analytics');

		$this->_profilesTable = $this->_dbPrefix.'profiles';
		$this->_usersToProfilesTable = $this->_dbPrefix.'rUsersProfiles';
		$this->_usersTable = $this->_dbPrefix.'users';
	}


	/**
	 * @param $idUser
	 * @param $idFB
	 * @param $accounts
	 */
	public function addProfilesToUser($idUser, $profiles)
	{
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

			$this->_db->insert($this->_usersToProfilesTable, array(
				'userId'	=> $idUser,
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
	public function account($idAccount)
	{
		return $this->_db->fetchRow('
			SELECT *
			FROM '.$this->_accountsTable.'
			WHERE id = ?',
			array($idAccount));
	}

	/**
	 * @param $idUser
	 * @return array
	 */
	public function accounts($idUser)
	{
		return $this->_db->fetchPairs('
			SELECT a.id, a.name
			FROM '.$this->_accountsTable.' a
			LEFT JOIN '.$this->_usersToAccountsTable.' ota ON (a.id = ota.idAccount)
			WHERE ota.idUser = ?',
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