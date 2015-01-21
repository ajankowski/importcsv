<?php
/**
 *  Import CSV Model.
 */
class ImportCsv extends CFormModel
{

  /**
   * Inserts new rows into database.
   *
   * @param string $table
   *   Table name.
   * @param array $linesArray
   *   Values from csv.
   * @param array $columns
   *   List of csv columns.
   * @param array $tableColumns
   *   List of table columns.
   */
  public function InsertAll($table, $linesArray, $columns, $tableColumns)
  {

    $columnsLength   = sizeof($columns); // size of columns array
    $tableString = ''; // rows in table
    $csvString   = ''; // items in csv
    $n = 0;
    $linesLength = sizeof($linesArray); // size of line for insert array

    // Watching all strings in array.
    for ($k=0; $k<$linesLength; $k++) {

      //Watching all columns in POST.
      $n_in = 0;

      for ($i=0; $i<$columnsLength; $i++) {
        if ($columns[$i] != '') {
          if ($k == 0) {
            $tableString = ($n!=0) ? $tableString.", ".$tableColumns[$i] : $tableColumns[$i];
          }

          if ($k == 0 && $n == 0) {
            $csvString = "(";
          }

          if ($k != 0 && $n_in == 0) {
            $csvString = $csvString."), (";
          }

          $csvString = ($n_in!=0) ? $csvString.", '".CHtml::encode(stripslashes($linesArray[$k][$columns[$i]-1]))."'" : $csvString."'".CHtml::encode(stripslashes($linesArray[$k][$columns[$i]-1]))."'";

          $n++;
          $n_in++;
        }
      }
    }

    $csvString = $csvString.")";

    // Insert $csvString to database.
    $sql = "INSERT INTO " . $table . "(" . $tableString . ") VALUES " . $csvString . "";
    $command = Yii::app()->db->createCommand($sql);

    if($command->execute()) {
      return (1);
    }
    else {
      return (0);
    }
  }

  /**
   * Update old rows.
   *
   * @param string $table
   *   Table name.
   * @param array $csvLine
   *   One line from csv.
   * @param array $columns
   *   List of csv columns.
   * @param array $tableColumns
   *   List of table columns.
   * @param string $needle
   *   Value to compare to csv.
   * @param string $tableKey
   *   Key for the compare table
   *
   * @return boolean
   *   True if passed. False if failed.
   */
  public function updateOld($table, $csvLine, $columns, $tableColumns, $needle, $tableKey)
  {

    $columnsLength = sizeof($columns); // size of columns array
    $tableString = '';
    $n = 0;

    for ($i=0; $i<$columnsLength; $i++) {
      if ($columns[$i]!='') {
        if ($n != 0) {
          $tableString . ", " . $tableColumns[$i] . "='" . CHtml::encode(stripslashes($csvLine[$columns[$i]-1])) . "'";
        }
        else {
          $tableColumns[$i] . "='" . CHtml::encode(stripslashes($csvLine[$columns[$i]-1])) . "'";
        }
        $n++;
      }
    }

    // Update row in database.
    $sql="UPDATE ".$table." SET ".$tableString." WHERE ".$tableKey."='".$needle."'";
    $command=Yii::app()->db->createCommand($sql);

    if ($command->execute()) {
      return (1);
    }
    else {
      return (0);
    }
  }

  /**
   * Get columns from a table.
   *
   * @param string $table
   *   The table name.
   *
   * @return object
   *   List of columns.
   */
  public function tableColumns($table)
  {
    return Yii::app()->getDb()->getSchema()->getTable($table)->getColumnNames();
  }

  /**
   * Get attribute from all rows from selected table.
   *
   * @param string $table
   *   Table name.
   * @param  string $attribute
   *   Columns in db table.
   *
   * @return object
   *   Query result.
   */
  public function selectRows($table, $attribute)
  {
    $sql = "SELECT " . $attribute . " FROM " . $table;
    $command = Yii::app()->db->createCommand($sql);
    return ($command->queryAll());
  }
}
