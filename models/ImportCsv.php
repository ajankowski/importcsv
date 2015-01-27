<?php
/**
 *  Import CSV Model.
 */
class ImportCsv extends CFormModel
{
  public $insertCounter = 0;
  public $insertArray = array();
  public $perRequest;
  public $table;
  public $columns;
  public $tableColumns;
  public $not_imported;
  public $lengthFile;
  public $oldItems;
  public $csvKey;
  public $tableKey;

  /**
   * Import Modes
   */
  const MODE_IMPORT_ALL = 1;
  const MODE_INSERT_NEW = 2;
  const MODE_INSERT_NEW_REPLACE_OLD = 3;

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

    // If there is a blank line just stop here.
    if ($linesLength == 0) {
      return (0);
    }

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
    $columnsLength = sizeof($columns);
    $tableString = '';
    $n = 0;

    for($i=0; $i<$columnsLength; $i++) {
      if($columns[$i]!='') {
        if ($n!=0) {
          $tableString = $tableString.", ".$tableColumns[$i]."='".CHtml::encode(stripslashes($csvLine[$columns[$i]-1]))."'";
        }
        else {
          $tableString = $tableColumns[$i]."='".CHtml::encode(stripslashes($csvLine[$columns[$i]-1]))."'";
        }
        $n++;
      }
    }

    // update row in database
    $sql = "UPDATE " . $table . " SET " . $tableString . " WHERE " . $tableKey . "='" . $needle . "'";
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

  /**
   * Insert all data into database.
   *
   * @param array $csvLine
   *  The csv line.
   * @param integer $count
   *  The line number.
   */
  public function insertAllIntoDatabase($csvLine, $count)
  {
    $this->insertArray[] = $csvLine;
    $this->insertCounter++;

    if ($this->insertCounter == $this->perRequest || $count == $this->lengthFile - 1) {
      $import = $this->InsertAll($this->table, $this->insertArray, $this->columns, $this->tableColumns);
      $this->insertCounter = 0;
      $this->insertArray = array();

      if ($import != 1) {
        $this->not_imported[] = $count;
      }
    }
  }

  /**
   * Insert only new data into database.
   *
   * @param array $csvLine
   *  The csv line.
   * @param integer $count
   *  The line number.
   */
  public function insertNewIntoDatabse($csvLine, $count)
  {
    if ($csvLine[$this->csvKey - 1] == '' || !$this->searchInOld($this->oldItems, $csvLine[$this->csvKey - 1], $this->tableKey)) {
      $this->insertArray[] = $csvLine;
      $this->insertCounter++;
      if ($this->insertCounter == $this->perRequest || $count == $this->lengthFile - 1) {
        $import = $this->InsertAll($this->table, $this->insertArray, $this->columns, $this->tableColumns);
        $this->insertCounter = 0;
        $this->insertArray = array();

        if ($import != 1) {
          $this->not_imported[] = $i;
        }
      }
    }
  }

  /**
   * Insert new and replace old itemes;
   *
   * @param array $csvLine
   *  The csv line.
   * @param integer $count
   *  The line number.
   */
  public function insertNewReplaceOldIntoDatabse($csvLine, $count)
  {
    if ($csvLine[$this->csvKey - 1] == '' || !$this->searchInOld($this->oldItems, $csvLine[$this->csvKey - 1], $this->tableKey)) {
      // insert new
      $this->insertArray[] = $csvLine;
      $this->insertCounter++;
      if ($this->insertCounter == $this->perRequest || $count == $this->lengthFile - 1) {
        $import = $this->InsertAll($this->table, $this->insertArray, $this->columns, $this->tableColumns);
        $this->insertCounter = 0;
        $this->insertArray = array();

        if ($import != 1) {
          $this->not_imported[] = $count;
        }
      }
    }
    else {
      // Replace old.
      $import = $this->updateOld($this->table, $csvLine, $this->columns, $this->tableColumns, $csvLine[$this->csvKey - 1], $this->tableKey);
      if ($import != 1) {
        $this->not_imported[] = $count;
      }
    }
  }

  /**
   * Search need in old rows.
   *
   * @param array $array
   *   Old itmes from database.
   * @param  string
   *   Old value from databse.
   *
   * @return boolean $return
   */
  public function searchInOld($array, $needle, $key) {
    $return = false;
    $arrayLength = sizeof($array);
    for ($i = 0; $i < $arrayLength; $i++) {
      if ($array[$i][$key] == $needle) {
        $return = true;
      }
    }

    return $return;
  }

  /**
   * Make any modifications before csv line gets processed.
   */
  public function modifyCsvLine(&$csvLine) {
    /*
    // Create a class that implements ImportCsv and over write this method.
    // Below is an example to modify a field.

    $columnsLength = sizeof($this->columns);

    for($i=0; $i<$columnsLength; $i++) {
      if($this->columns[$i]!='') {
        switch($this->tableColumns[$i]) {
          case 'zipcode':
            $csvLine[$this->columns[$i]-1] = '66666';
            break;
        }
      }
    }
    */
  }
}
