
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