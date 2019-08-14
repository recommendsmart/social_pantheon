CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Usage
 * Maintainers

INTRODUCTION
------------

This module allows you to switch topics for different conditions.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/clever_theme_switcher

REQUIREMENTS
------------

This module is dependent of 
https://www.drupal.org/project/mobile_device_detection.

INSTALLATION
------------

 * Module: Install as you would normally install a contributed Drupal module.
   See: https://www.drupal.org/documentation/install/modules-themes/modules-8
   for further information.

CONFIGURATION
-------------

The module has a menu in the "Appearance" section. In the 
"Clever Theme Switcher" tab you can create your own conditions for displaying 
the topic.

USAGE
-------------

  First step.

  You must create your <b>ConditionPlugin</b> plugin. This plugin can have 
  any logic. In any case, it will return either the TRUE or the FALSE.

  Second step

  Create your EventSubscriber and register your plugin in it. 
  $request->attributes->set("_cts_plugin_handler",[]);

MAINTAINERS
-----------

Current maintainers:
 * Victor Isaikin - https://www.drupal.org/u/depthinteractive
 * Site - https://depthinteractive.ru
