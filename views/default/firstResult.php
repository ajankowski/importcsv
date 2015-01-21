<?php
/**
 * @file
 * First result file.
 */

if ($error == 1) {
  // First error: Unable to upload file.
  echo("<span class='importCsvError'>".Yii::t('importcsvModule.importcsv', 'Error').": ".Yii::t('importcsvModule.importcsv', 'Unable to upload file')."</span>");
}
elseif ($error == 2) {
  // Second error: Download file is not a .csv.
  echo("<span class='importCsvError'>".Yii::t('importcsvModule.importcsv', 'Error').": ".Yii::t('importcsvModule.importcsv', 'Download file is not a .csv')."</span>");
}
elseif ($error == 0) {
  // No errors. Going to second step
  ?>
  <script type="text/javascript">
    window.parent.toSecondStep("<?php echo $uploadfile;?>", "<?php echo $delimiterFromFile;?>", "<?php echo $tableFromFile;?>", '<?php echo $textDelimiterFromFile;?>');
  </script>
  <?php
}
