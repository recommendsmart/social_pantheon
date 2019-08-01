<?php

namespace Drupal\if_then_else\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Class defined for IfthenelseRule entity.
 *
 * @ConfigEntityType(
 *   id ="ifthenelserule",
 *   label = @Translation("Ifthenelse Rule"),
 *   config_prefix = "ifthenelserule",
 *   handlers = {
 *     "list_builder" = "Drupal\if_then_else\IfthenelseRuleListBuilder",
 *     "form" = {
 *       "add" = "Drupal\if_then_else\IfthenelseRuleForm",
 *       "edit" = "Drupal\if_then_else\IfthenelseRuleForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *       "disableAll" = "Drupal\if_then_else\Form\IfthenelseRuleDisableAllForm"
 *     }
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "weight" = "weight"
 *   },
 *   links = {
 *     "clone" = "/if_then_else/{ifthenelserule}/clone",
 *     "disable" = "/admin/config/system/ifthenelse/manage/{ifthenelserule}/disable",
 *     "disable-all" = "/admin/config/system/ifthenelse/rule/disable-all",
 *     "enable" = "/admin/config/system/ifthenelse/manage/{ifthenelserule}/enable",
 *     "delete-form" = "/admin/config/system/ifthenelse/manage/{ifthenelserule}/delete",
 *     "edit-form" = "/admin/config/system/ifthenelse/manage/{ifthenelserule}",
 *     "collection" = "/admin/config/system/ifthenelse-rules",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "active",
 *     "module",
 *     "event",
 *     "condition",
 *     "data",
 *     "processed_data",
 *     "weight",
 *   }
 * )
 */
class IfthenelseRule extends ConfigEntityBase implements IfthenelseRuleInterface {

  /**
   * The announcement's message.
   *
   * @var string
   */
  protected $rules;

  /**
   * Ifthenelse rule ID.
   *
   * @var string
   */
  public $id;

  /**
   * {@inheritdoc}
   */
  public function setActive($status) {
    $this->set('active', $status);
    return $this;
  }

}