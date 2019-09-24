<?php

namespace Drupal\dfinance\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Account Code type entity.
 *
 * @ConfigEntityType(
 *   id = "financial_account_code_type",
 *   label = @Translation("Account Code type"),
 *   label_collection = @Translation("Account Code types"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\dfinance\Controller\AccountCodeTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\dfinance\Form\AccountCodeTypeForm",
 *       "edit" = "Drupal\dfinance\Form\AccountCodeTypeForm",
 *       "delete" = "Drupal\dfinance\Form\AccountCodeTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "financial_account_code_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "financial_account_code",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/finance/financial_account_code/type/{financial_account_code_type}",
 *     "add-form" = "/admin/finance/financial_account_code/type/add",
 *     "edit-form" = "/admin/finance/financial_account_code/type/{financial_account_code_type}/edit",
 *     "delete-form" = "/admin/finance/financial_account_code/type/{financial_account_code_type}/delete",
 *     "collection" = "/admin/finance/financial_account_code/type"
 *   }
 * )
 */
class AccountCodeType extends ConfigEntityBundleBase implements AccountCodeTypeInterface {

  /**
   * The Account Code type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Account Code type label.
   *
   * @var string
   */
  protected $label;

}
