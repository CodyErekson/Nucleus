<?php

namespace Nucleus\Helpers;

use Respect\Validation\Validatable as Respect;
use Respect\Validation\Exceptions\NestedValidationException;

class Validator
{
	protected $errors;
	protected $container;

	public function __construct(\Slim\Container $container)
	{
		$this->container = $container;
	}

	public function validate($request, array $rules)
	{
		foreach ($rules as $field => $rule){
			try {
				$rule->setName(ucfirst($field))->assert($request->getParam($field));
			} catch (NestedValidationException $e) {
				$this->errors[$field] = $e->getMessages();
				$this->container['error.log']->error("Validation error for " . $field, $e->getMessages());
			}
		}

		$_SESSION['errors'] = $this->errors;

		return $this;
	}

	public function failed()
	{
		return !empty($this->errors);
	}
}