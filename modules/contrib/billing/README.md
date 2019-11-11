# Billing

## Currency
 * Module require: https://www.drupal.org/project/currency
 * Add currency here: /admin/config/regional/currency


## API Usage:
see: `Drupal\billing\Form\AddCorrection`
### User Correcttion, default currency.
```php
<?php
$uid = 2;
$sum = 10;
$comment = "some info";
$transaction = [
  'sum' => floatval($sum),
  'account' => Drupal\billing\Controller\BillingAccountManager::getUserAccount($uid),
];
$deal = Drupal\billing\Controller\BillingTransactionManager::deal($transaction, $comment);
```
### Node To CurrentUser USD.
```php
<?php
$nid = 4;
$sum = 10;
$comment = "some info";
$currency = 'USD';
$transaction = [
  'sum' => floatval($sum),
  'debit_account' => Drupal\billing\Controller\BillingAccountManager::getCurrentAccount($currency),
  'credit_account' => Drupal\billing\Controller\BillingAccountManager::getAccount('node', $nid, $currency),
];
$deal = Drupal\billing\Controller\BillingTransactionManager::deal($transaction, $comment);
```
