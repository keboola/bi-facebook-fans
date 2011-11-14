<?
/**
 * @author Jakub Matejka <jakub@keboola.com>
 */
class App_Connector_Facebook extends App_Connector
{
	/**
	 * @var string
	 */
	private $_accountsTable;

	/**
	 * @var string
	 */
	private $_usersToAccountsTable;

	/**
	 * @var string
	 */
	private $_usersTable;



	public function __construct()
	{
		parent::__construct('facebook');

		$this->_accountsTable = $this->_dbPrefix.'accounts';
		$this->_usersToAccountsTable = $this->_dbPrefix.'rUsersAccounts';
		$this->_usersTable = $this->_dbPrefix.'users';
	}


	/**
	 * @param $idUser
	 * @param $idFB
	 * @param $accounts
	 */
	public function addAccountsToUser($idUser, $idFB, $accounts)
	{
		foreach($accounts as $id => $account) {
			$idAccount = $this->_db->fetchOne('SELECT id FROM '.$this->_accountsTable.' WHERE idFB=?', $id);
			if (!$idAccount) {
				$this->_db->insert($this->_accountsTable, array(
					'name'		=> $account['name'],
					'idFB'		=> $id
				));
				$idAccount = $this->_db->lastInsertId($this->_accountsTable);
			}

			$isUserSaved = $this->_db->fetchOne('SELECT COUNT(*) FROM '.$this->_usersTable.' WHERE id = ?', $idUser);
			if(!$isUserSaved) {
				$this->_db->insert($this->_usersTable, array('id' => $idUser));
			}

			$this->_db->insert($this->_usersToAccountsTable, array(
				'idUser'		=> $idUser,
				'idAccount'		=> $idAccount,
				'idFBUser'		=> $idFB,
				'oauthToken'	=> $account['token']
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
			SET oauthToken = ?
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