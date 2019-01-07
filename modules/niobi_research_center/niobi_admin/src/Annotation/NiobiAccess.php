<?php

namespace Drupal\niobi_admin\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a NiobiAccess annotation object.
 *
 * Plugin Namespace: Plugin\niobi_admin
 *
 * @see plugin_api
 *
 * @Annotation
 */
class NiobiAccess extends Plugin {

    /**
     * The plugin ID.
     *
     * @var string
     */
    public $id;

    /**
     * The human-readable name of the NiobiAccess plugin.
     *
     * @ingroup plugin_translatable
     *
     * @var \Drupal\Core\Annotation\Translation
     */
    public $label;

    /**
     * The category under which the NiobiAccess should be listed in the UI.
     *
     * @var \Drupal\Core\Annotation\Translation
     *
     * @ingroup plugin_translatable
     */
    public $category;

}
