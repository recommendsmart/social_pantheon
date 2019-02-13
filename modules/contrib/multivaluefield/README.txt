
Module : multivaluefield

@TODO

Create multivaluefield, (like Image module) able to contains multiple values as : KEY:VALUE:OPTIONS ....
Also See paragraphs and field_collection to inspire the functionality

Inspire storage/JSON From link module

- Create field dynamicly with any type of field
- Create entity multivaluefield
- IF a field is instanciate programaticly, add/remove when module install/uninstall

TODO
- Resolve pb of the empty value on submit (system does not remove fields with empty values), DONE, to test

@Tests
#Install / Uninstall
drush myx delete
drush cron
drush pmu multivaluefield -y
drush en multivaluefield -y
drush myx create
drush myx nc
drush myx nup
drush cr


drush en multivaluefield_example -y

drush field-create page field_mvf,multivaluefield,multivaluefield_widget
drush field-create page field_test,link,link_default

 Fatal error:


Add / Create programaticly
Example 1.
\Drupal\node\Entity\Node::create(array(
  'type' => 'page',
  'title' => 'test mvf',
  'status' => 1,
  'field_mvf' => [
    'index' => 'test',
    '0' => "val F 1",
    '1' => "val F 2",
  ],
))->save();

Example 2
$entity = \Drupal\node\Entity\Node::load(5);
$values = [
  'index' => 'test',
  '0' => "val F 1",
  '1' => "val F 2",
];
$entity->set('field_mvf', [$values, $values]);
$entity->save();

CODER (HELP https://www.drupal.org/node/1419988)
-----
Check Drupal coding standards
phpcs --standard=Drupal --extensions=php,module,inc,install,test,profile,theme modules/drupal/multivaluefield
Check Drupal best practices
phpcs --standard=DrupalPractice --extensions=php,module,inc,install,test,profile,theme modules/drupal/multivaluefield
Automatically fix coding standards
phpcbf --standard=Drupal --extensions=php,module,inc,install,test,profile,theme modules/drupal/multivaluefield


TODO
- add / Correct radio buttons and checkboxes
- Render select list lable instend of key.

CHANGELOG
2017-03-01
----------
1.0.6
- Add Index field position
- Improve Field settings
