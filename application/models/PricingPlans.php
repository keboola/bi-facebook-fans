<?php
/**
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-11-09
 */

class Model_PricingPlans extends App_Db_Table
{
	protected $_name = 'pricingPlans';
	protected $_dependentTables = array('Model_UsersToConnectors');

	/**
	 * @return array
	 */
	public function plansByPages()
	{
		$plans = array();
		foreach($this->fetchAll(null, 'pagesCount ASC') as $p) {
			$plans[$p->pagesCount] = $p->toArray();
		}
		return $plans;
	}
}

