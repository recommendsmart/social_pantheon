<?php

namespace Drupal\prepopulate\Prepopulators;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Base class for prepopulators.
 */
abstract class Prepopulator {

  /**
   * The list of HTTP(S) request parameters.
   *
   * @var array
   */
  protected $requestParameters;

  /**
   * Class constructor.
   *
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestParameters = $request_stack->getCurrentRequest()->query->all();
  }

  /**
   * Prepopulate the entity with the request parameters.
   */
  public function prepopulate() {
    foreach ($this->requestParameters as $parameter => $value) {
      if ($this->hasFormField($parameter)) {
        $this->setFormField($parameter, $value);
      }
    }
  }

  /**
   * Determines if this prepopulator has a particular form field.
   *
   * @param string $form_field
   *   The form field.
   *
   * @return bool
   *   TRUE if the form field exists; FALSE otherwise.
   */
  abstract protected function hasFormField(string $form_field);

  /**
   * Fills a particular form field with a value.
   *
   * @param string $form_field
   *   The form field.
   * @param string $value
   *   The value.
   */
  abstract protected function setFormField(string $form_field, string $value);

}
