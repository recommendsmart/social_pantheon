<?php

namespace Drupal\dfinance\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Financial Document type entity.
 *
 * @ConfigEntityType(
 *   id = "financial_doc_type",
 *   label = @Translation("Financial Document Type"),
 *   label_collection = @Translation("Financial Document Types"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\dfinance\Controller\FinancialDocTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\dfinance\Form\FinancialDocTypeForm",
 *       "edit" = "Drupal\dfinance\Form\FinancialDocTypeForm",
 *       "delete" = "Drupal\dfinance\Form\FinancialDocTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "financial_doc_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "financial_doc",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/finance/financial_doc_type/add",
 *     "edit-form" = "/admin/finance/financial_doc_type/{financial_doc_type}/edit",
 *     "delete-form" = "/admin/finance/financial_doc_type/{financial_doc_type}/delete",
 *     "collection" = "/admin/finance/financial_doc_type"
 *   }
 * )
 */
class FinancialDocType extends ConfigEntityBundleBase implements FinancialDocTypeInterface {

  /**
   * The Financial Document type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Financial Document type label.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this Financial Doc Type.
   *
   * @var string
   */
  protected $description;

  public function getDescription() {
    return $this->description;
  }

  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

}
