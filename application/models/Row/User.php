<?
/**
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-10-13
 */

class Model_Row_User extends Zend_Db_Table_Row_Abstract
{
	/**
	 * @return array
	 */
	public function subscribedConnectors()
	{
		$connectors = array();
		foreach($this->findDependentRowset('Model_UsersToConnectors') as $c) {
			$connectors[$c->idConnector] = $c->paidUntil;
		}
		return $connectors;
	}

	/**
	 * @param $idPlan
	 * @return string
	 */
	public function paypalToken($idPlan)
	{
		return $this->id.'-'.$idPlan.'-'.sha1($this->id.$idPlan.$this->salt);
	}
}