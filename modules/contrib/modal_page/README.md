# MODAL PAGE

## CONTENTS OF THIS FILE


 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


## INTRODUCTION

The Modal Page allow create Modal in CMS and set page to show. If the user visit
this page that configures it shows the modal.

* For a full description of the project, visit the project page:
   https://www.drupal.org/project/modal_page

* To submit bug reports and feature suggestions, or track changes:
   https://www.drupal.org/project/issues/modal_page

## REQUIREMENTS

No special requirements.


## INSTALLATION

* Install as you would normally. Visit
   https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
   for further information.


## CONFIGURATION

* Configure your messages in Administration » Structure » Modal

  Click in Add Modal

   1. Set the Title of modal.
   2. Set the Text of modal (Body).
   3. Set type of modal.
   4. Set pages or parameters to show the modal.
   5. Set text for OK label button.
   6. Choose modal language.
   7. Save.

## TESTS

* Before of run tests you needs create a shortcut for core/phpunit.xml.dist in
  your root project.

### EXECUTING UNITTESTS

```
vendor/bin/phpunit modules/modal_page
```

### EXECUTING KERNELTEST WITH LANDO

```
lando php core/scripts/run-tests.sh --php /usr/local/bin/php --url http://d8.lndo.site --dburl mysql://drupal8:drupal8@database/drupal8 --sqlite simpletest.sqlite --module modal_page --verbose --color
```


## MAINTAINERS

### Current maintainers:
 * Renato Gonçalves (RenatoG) - https://www.drupal.org/user/3326031
 * Thalles Ferreira (thalles) - https://www.drupal.org/user/3589086
