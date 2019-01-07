<?php
/**
 * @file
 * Provides Drupal\niobi_admin\NiobiAccessInterface;
.
 */
namespace Drupal\niobi_admin;
/**
 * An interface for all NiobiAccess type plugins.
 */
interface NiobiAccessInterface {
    /**
     * Provide a description of the plugin.
     * @return string
     *   A string description of the plugin.
     */
    public function description();
}
