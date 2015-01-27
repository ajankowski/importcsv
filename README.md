About
============

This is a fork of importcsv and can be found here:
  https://github.com/ajankowski/importcsv

Orignal repo by: Artem Demchenkov <lunoxot@mail.ru>.

ImportCSV is a Yii Framework extension that is used to import columns from a csv
file to a database.

Import occurs in three steps:

1. Upload file
2. Select delimiters and table
3. Select mode and columns in table

Module has 3 modes:

1. Insert all - Add all rows
2. Insert new - Add new rows. Old rows remain unchanged
3. Insert new and replace old - Add new rows. Old rows replace

All parameters from the previous imports will be saved in a special .php file in upload folder.

Options:
path - Required: The path to the folder for saving the csv file.
allowedTables - Optional: An array of table names. Useful if you don't want a
                user to accidentally import to an important table.
importCsvOverwrite - Optional: Allows another class (which extends ImportCsv) to
                     be called instead of ImportCsv in order to make changes.
                     Most cases your custom class will overwrite the
                     modifyCsvLine() method to make custom changes.
pathToSaveOldData - Optional: If a path is set it will save a csv of the table
                    before the import.


Requirements
============

Yii 1.1

Usage
============

1) Copy extension folder under /protected/modules.

2) Register this module in /protected/config/main.php

    'modules'=>array(
        .........
        'importcsv'=>array(
            'path'=>'upload/importCsv/', // path to folder for saving csv file and file with import params
            'allowedTables'=>array('table1','table3'), // Tables that are allowed.
            'importCsvOverwrite' => array('company' => 'ImportcsvCompanyImport'), // Overwrite class
        ),
        ......
    ),

3) Create a directory which you use in 'path'. Do not forget to set access permissions for directory 'path'.

4) The module is available here:

http://yourproject/importcsv.

Or here:

http://yourproject/index.php?r=importcsv.


Or somewhere else:-) It depends from path settings in your project;

5) ATTENTION! The first row of your csv-file must will be a row with column names.
