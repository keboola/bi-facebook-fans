<?php
/**
 * @author Jakub Matejka <jakub@keboola.com>
 */
class AccountController extends App_Controller_Action
{

	public function indexAction()
	{
		$_uc = new Model_UsersToConnectors();
		$_c = new App_Connector_Facebook();

		$connectors = array();

		$userToConnector = $_uc->fetchRow(array('idUser=?' => $this->_user->id, 'idConnector=?' => 1));
		if ($userToConnector) {
			$subscribedPlan = $userToConnector->findParentRow('Model_PricingPlans');
			$userAccounts = $_c->accounts($this->_user->id);

			$connectors[1] = array(
				'maxAccountsCount' => $subscribedPlan->accountsCount,
				'usedAccountsCount' => count($userAccounts),
				'maxUsersCount' => $subscribedPlan->usersCount,
				'usedUsersCount' => $userToConnector->findDependentRowset('Model_Invitations')->count()+1
			);
		}

		$this->view->connectors = $connectors;
	}

}
