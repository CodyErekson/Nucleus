<?php
/**
 * Handle validation using Respect Validator and pass returned failure messages into global error array
 */

namespace Nucleus\Helpers;

use Respect\Validation\Validatable as Respect;
use Respect\Validation\Exceptions\NestedValidationException;

/**
 * Class Validator
 * @package Nucleus\Helpers
 */
class Validator
{
	protected $errors;
	protected $container;

	public function __construct(\Slim\Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Validate provided data, put failure messages in global error array
	 * @param $request
	 * @param array $rules
	 * @return $this
	 */
	public function validate($request, array $rules)
	{
		foreach ($rules as $field => $rule){
			try {
				$rule->setName(ucfirst($field))->assert($request->getParam($field));
			} catch (NestedValidationException $e) {
				$this->errors[$field] = $e->getMessages();
				$this->container['debug.log']->debug("Validation error for " . $field, $e->getMessages());
			}
		}

		$_SESSION['errors'] = $this->errors;

		return $this;
	}

	/**
	 * Check if a validation failed
	 * @return bool
	 */
	public function failed()
	{
		return !empty($this->errors);
	}
}