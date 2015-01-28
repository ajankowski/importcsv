<?php
/**
 * @file
 * Module for importing a csv to the a database.
 */

class ImportcsvModule extends CWebModule
{
  /*
   * @var Path to csv file.
   */
  public $path;
  /**
   * @var Allowed tables to import to. This will prevent allowing a user to
   * import to an unwanted table.
   */
  public $allowedTables = array();
  /**
   * @var Allow the code to call your class which extends the ImportCsv Class
   * in order to make custom modifications. Set the overwrite to a table.
   *   array('table_name' => 'overwrite class').
   */
  public $importCsvOverwrite = array();
  /**
   * @var Path to save old data.
   */
  public $pathToSaveOldData = FALSE;
  /**
   * @var An array containing characters to keep when inserting into database
   */
  public $htmlDecodeBeforeDatabaseInsert = array(
    '&amp;' => '&',
  );

  public function init()
  {
    // this method is called when the module is being created
    // you may place code here to customize the module or the application

    // import the module-level models and components
    $this->setImport(array(
      'importcsv.models.*',
      'importcsv.components.*',
    ));
  }

  public function beforeControllerAction($controller, $action)
  {
    if(parent::beforeControllerAction($controller, $action))
    {
      // this method is called before any module controller action is performed
      // you may place customized code here
      return true;
    }
    else
      return false;
  }
}
