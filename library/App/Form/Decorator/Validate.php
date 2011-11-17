<?php
/**
 * jQuery validation
 *
 * @author Jakub Matejka <jakub@keboola.com>
 */
class App_Form_Decorator_Validate extends Zend_Form_Decorator_Abstract
{

	public function render($content)
	{
		$translator = Zend_Registry::get('Zend_Translate');
		$elements = $this->getElement()->getElements();

		$validation = array();
		foreach($elements as $elementName => $element) {
			$validators = $element->getValidators();
			if(count($validators)) {

				$elementRules = array();
				foreach($validators as $validatorName => $validator) {
					switch ($validatorName) {
						case 'Zend_Captcha_Image':
						case 'Zend_Validate_NotEmpty':
							$elementRules['required'] = 'true';
							break;
						case 'Zend_Validate_Date':
							$elementRules['dateDE'] = 'true';
							break;
						case 'Zend_Validate_Digits':
							$elementRules['digits'] = 'true';
							break;
						case 'Zend_Validate_EmailAddress':
							$elementRules['email'] = 'true';
							break;
					}
				}

				if (count($elementRules))
					$validation[$elementName] = $elementRules;
			}
		}

		$formName = $this->getElement()->getName();
		if(!$formName) {
			$formName = 'form';
		} else {
			$formName = '#'.$formName;
		}

		if (count($validation)) {
			$output = "\n\t$('{$formName}').validate({\n"
				. "\t\terrorElement: 'span',\n"
				. "\t\twrapper: 'strong',\n";

			$outputRules = '';
			$outputMessages = '';
			$elementsPosition = 0;
			foreach ($validation as $name => $rules) {
				if($name=='captcha')
					$name .= '[input]';
				$name = "'".$name."'";
				$outputRules .= "\t\t\t{$name}: {\n";
				$outputMessages .= "\t\t\t{$name}: {\n";
				$validatorsPosition = 0;
				foreach ($rules as $ruleName => $ruleValue) {
					$outputRules .= "\t\t\t\t".$ruleName.": ".$ruleValue;
					$outputMessages .= "\t\t\t\t".$ruleName.": '".$translator->translate('jquery.validation.'.$ruleName)."'";
					if ($validatorsPosition != count($rules)-1) {
						//include trailing comma only if it's not last row
						$outputRules .= ",";
						$outputMessages .= ",";
					}
					$outputRules .= "\n";
					$outputMessages .= "\n";

					$validatorsPosition++;
				}
				$outputRules .= "\t\t\t}";
				$outputMessages .= "\t\t\t}";
				if ($elementsPosition != count($validation)-1) {
					//include trailing comma only if it's not last row
					$outputRules .= ",";
					$outputMessages .= ",";
				}
				$outputRules .= "\n";
				$outputMessages .= "\n";

				$elementsPosition++;
			}

			$output .= "\t\trules: {\n{$outputRules}\n\t\t},\n"
				. "\t\tmessages: {\n{$outputMessages}\n\t\t}\n"
				. "\t});";

			$this->getElement()->getView()->jQuery()
				->addJavascriptFile('/js/jquery.validate/jquery.validate.min.js')
				->addOnLoad($output);
		}

		return $content;
	}
}