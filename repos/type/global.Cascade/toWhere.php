<?php
ob_start ();
?>
<?= $field->getTable () ?>.<?= $field->getColumn () ?> IN (
WITH RECURSIVE __all_<?= $field->getColumn () ?> AS (
SELECT <?= $field->getLinkColumn () ?> FROM <?= $field->getLink () ?> WHERE <?= $field->getFatherColumn () ?> = '<?= $field->getValue () ?>' OR <?= $field->getLinkColumn () ?> = '<?= $field->getValue () ?>'
UNION ALL
SELECT b.<?= $field->getLinkColumn () ?> FROM __all_<?= $field->getColumn () ?> a, <?= $field->getLink () ?> b WHERE a.<?= $field->getLinkColumn () ?> = b.<?= $field->getFatherColumn () ?>
) SELECT <?= $field->getLinkColumn () ?> FROM __all_<?= $field->getColumn () ?>)
<?php
return ob_get_clean ();
?>