<?php

require_once 'Zend/Form/Decorator/Abstract.php';

class App_Form_Decorator_CustomErrors extends Zend_Form_Decorator_Abstract
{
	/**
	 * Render errors
	 *
	 * @param  string $content
	 * @return string
	 */
	public function render($content)
	{
		$element = $this->getElement();
		$view    = $element->getView();
		if (null === $view) {
			return $content;
		}

		$errors = $element->getMessages();
		if (empty($errors)) {
			return $content;
		}

		$separator = $this->getSeparator();
		$placement = $this->getPlacement();

		$view->addHelperPath('App/View/Helper', 'App_View_Helper');
		$errors    = $view->customFormErrors($errors, $this->getOptions());

		switch ($placement) {
			case self::APPEND:
				return $content . $separator . $errors;
			case self::PREPEND:
				return $errors . $separator . $content;
		}
	}
}
