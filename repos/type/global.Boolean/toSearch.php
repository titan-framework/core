<? ob_start () ?>
<input type="radio" name="<?= $fieldName ?>" id="<?= $fieldId ?>_true" value="1"  <?= $field->getValue () === TRUE ? 'checked' : '' ?> /> <?= __ ('Yes') ?>
&nbsp;&nbsp;
<input type="radio" name="<?= $fieldName ?>" id="<?= $fieldId ?>_false" value="0"  <?= $field->getValue () === FALSE ? 'checked' : '' ?> /> <?= __ ('No') ?>
&nbsp;&nbsp;
<input type="radio" name="<?= $fieldName ?>" id="<?= $fieldId ?>_disabled" value="-1" <?= $field->isEmpty () ? 'checked' : '' ?> /> <?= __ ('Disable') ?>
<? return ob_get_clean () ?>