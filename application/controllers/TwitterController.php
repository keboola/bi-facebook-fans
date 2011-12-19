<?
/**
 * @author
 */
class TwitterController extends App_Controller_Action
{
	/**
	 * Id of connector in connectors table
	 */
	const ID_CONNECTOR = 3;

	/**
	 * @var App_Connector_GoogleAnalytics
	 */
	private $_connector;

	public function init()
	{
		parent::init();

		//$this->_connector = new App_Connector_Twitter();
	}

	public function indexAction()
	{
		//@TODO Info o konektoru
		$this->_helper->layout->setLayout('simple');
		if($this->_request->iframe==1) {
			$this->view->iframe = TRUE;
		}
	}

	public function registerAction()
	{
		//@TODO registrace účtů
	}

}