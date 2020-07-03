CONTENTS OF THIS FILE
---------------------

* Introduction
* Requirements
* Installation
* Configuration
* Troubleshooting
* Maintainers

INTRODUCTION
------------

The Announcement modal module provides the site administrator to configure the
site wide announcements for their customers or users.This will help for the
customers or users to know the important informations.

Example: For a business site because of some problem they might not be able to
deliver the products to their clients. In that case they can enable the
announcements for their clients.

 * For downloading the module, visit the project page:
   https://www.drupal.org/project/announcement_modal

 * To submit bug reports and feature suggestions, or track changes:
   https://www.drupal.org/project/issues/announcement_modal

REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
  https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* After enabling the module please go to the Administration » Configuration
  » System » Announcement modal Configuration. Add the necessary settings here.
  Note : make sure you enabled Show Banner settings if no announcements will
         not be shown.

* After setting all the configurations you can go to Administration » Structure
  » Block Layout. Place the block in a content region where your site’s
  main content exists make sure you uncheck the Display Title selection.

* Once all is done , Clear all cache and visit the site. Now you will be able
  to see the modal window loading for the first time. When user closes the
  modal a small floating button will be shown on the right side of the page.
  This button will toggle the modal window.

TROUBLESHOOTING
---------------

* After everything is configured if the user will not be able to get the
  modal announcement. Check the configuration page if you are missing any
  settings like show banner or date validation.
* Sometimes images will not get updated as soon as you update the
  configuration might need to clear the site cache once.

MAINTAINERS
-----------

Current maintainer:
* Akshay Devadiga (akshay_d) - https://www.drupal.org/user/3580858
