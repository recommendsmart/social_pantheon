<?php

namespace Drupal\if_then_else\Controller;

use Drupal\Core\Url;
use Drupal\if_then_else\Entity\IfthenelseRuleInterface;
use Drupal\if_then_else\Event\EventFilterEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * If Then Else Central Controller class.
 */
class IfThenElseController {

  /**
   * Returns all rule config for a specific event.
   *
   * @param string $event_name
   *   Event name.
   *
   * @return array
   *   An array of applicable if then else rules.
   */
  public static function eventPresent($event_name, $args) {
    $query = \Drupal::entityQuery('ifthenelserule')
      ->condition('active', TRUE)
      ->condition('event', $event_name)
      ->sort('weight', 'DESC');

    $event_filter_event = new EventFilterEvent($query, $args);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch('if_then_else_' . $event_name . '_event_filter_event', $event_filter_event);

    $enabled_rule_ids = $query->execute();

    return \Drupal::entityTypeManager()->getStorage('ifthenelserule')->loadMultiple($enabled_rule_ids);
  }

  /**
   * Central Process function.
   */
  public static function process($event_name, array $input) {
    // Checking for an event in rules.
    $rules = static::eventPresent($event_name, $input);

    // Return if no rule has a form alter event.
    if (empty($rules)) {
      return;
    }

    $node_objects = [];
    $nids_to_be_skipped = [];
    foreach ($rules as $id => $rule) {
      $data = json_decode($rule->data);
      $processed_data = unserialize($rule->processed_data);
      foreach ($processed_data['execution_order'] as $nid) {
        if (in_array($nid, $nids_to_be_skipped)) {
          continue;
        }

        $node = $data->nodes->{$nid};
        $class = $node->data->class;

        if (!class_exists($class)) {
          return;
        }

        /** @var \Drupal\if_then_else\core\Nodes\Node $node_obj */
        $node_obj = new $class();
        $node_obj->setData($node->data);
        if ($node->data->type == 'event') {
          $node_obj->setInputs($input);
        }
        elseif (count((array) $node->inputs)) {
          // Get outputs from precedent nodes.
          $inputs = [];
          foreach ($node->inputs as $socket_name => $output) {
            if (count($output->connections)) {
              foreach ($output->connections as $connection) {
                /** @var \Drupal\if_then_else\core\Nodes\Node $precedent_node_object */
                $precedent_node_object = $node_objects[$connection->node];
                $precedent_socket_name = $connection->output;
                $precedent_node_outputs = $precedent_node_object->getOutputs();
                $inputs[$socket_name] = &$precedent_node_outputs[$precedent_socket_name];
              }
            }
          }

          if (array_key_exists('execute', $inputs) && $inputs['execute'] === FALSE) {
            // Node shouldn't be executed.
            // Even its dependent nodes should not be executed.
            $nids_to_be_skipped = array_unique(array_merge($nids_to_be_skipped, $processed_data['dependent_nids'][$nid]));
            continue;
          }
          else {
            $node_obj->setInputs($inputs);
          }
        }

        $node_obj->process();

        if ($node->data->type == 'action' || $node->data->type == 'event') {
          $outputs = $node_obj->getOutputs();
          if ($outputs == NULL || !array_key_exists('success', $outputs)) {
            $node_obj->setSuccess(TRUE);
          }
        }
        $node_objects[$nid] = $node_obj;
      }

      // Check if debuggin is enabled for rules.
      $debugging = \Drupal::config('if_then_else.adminsettings')->get('enable_debugging');
      if ($debugging) {
        $messenger = \Drupal::messenger();
        $messenger->addMessage(t('Ifthenelse rule @rule triggered', ['@rule' => $rule->label]), $messenger::TYPE_STATUS);
      }
    }
  }

  /**
   * Disable route callback to disbale the IfThenElseRules.
   */
  public function disable(IfthenelseRuleInterface $ifthenelserule = NULL) {
    $ifthenelserule->setActive(FALSE);
    $ifthenelserule->save();
    \Drupal::messenger()->addMessage(t('%rule_label is disabled.', ['%rule_label' => $ifthenelserule->label()]));
    return new RedirectResponse($ifthenelserule->toUrl('collection')->toString());
  }

  /**
   * Enable route callback to enable the IfThenElseRules.
   */
  public function enable(IfthenelseRuleInterface $ifthenelserule = NULL) {
    $ifthenelserule->setActive(TRUE);
    $ifthenelserule->save();
    \Drupal::messenger()->addMessage(t('%rule_label is enabled.', ['%rule_label' => $ifthenelserule->label()]));
    return new RedirectResponse($ifthenelserule->toUrl('collection')->toString());
  }

  /**
   * Enable route callback to enable the IfThenElseRules.
   */
  public function entityClone(IfthenelseRuleInterface $ifthenelserule = NULL) {

    $clone_rule = $ifthenelserule->createDuplicate();
    $clone_rule->id = $ifthenelserule->id() . '_clone';
    $clone_rule->label = $ifthenelserule->label() . '_clone';
    $clone_rule->save();
    \Drupal::messenger()->addMessage(t('%rule_label is duplicated.', ['%rule_label' => $ifthenelserule->label()]));
    $path = Url::fromRoute('entity.ifthenelserule.edit_form',
      ['ifthenelserule' => $clone_rule->id])->toString();
    return new RedirectResponse($path);
  }

}
