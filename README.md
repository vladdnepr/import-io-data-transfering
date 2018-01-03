# Transfering Import.IO extractor data to the MySQL table

This library transfer Import.IO extractor data to the MySQL table.

MySQL table will creating automatically based on fields names.

## Installation

    $ git clone git@github.com:vladdnepr/import-io-data-transfering.git
    $ composer install

## Usage

From CLI:

    $ php ./bin/transfer --key IMPORT_IO_KEY --id IMPORT_IO_EXTRACTOR_ID --mhost MYSQL_HOST --muser MYSQL_USER --mpass MYSQL_PASS --mdb MYSQL_DB --mtable MYSQL_TABLE
    
or build PHAR file:

    $ php ./vendor/bin/phar-composer build .
    $ php ./importio.phar --key IMPORT_IO_KEY --id IMPORT_IO_EXTRACTOR_ID --mhost MYSQL_HOST --muser MYSQL_USER --mpass MYSQL_PASS --mdb MYSQL_DB --mtable MYSQL_TABLE
    
or like library:

    $request = new \VladDnepr\ImportIO\ServiceRequest(
        'IMPORTIO_KEY',
        'IMPORTIO_EXTRACTOR_ID'
    );
    
    $response = $request->getResponse();
    
    if ($errors = $request->getErrors()) {
        exit("Request was have errors: " . implode("\n", $errors) . "\n");
    }
    
    if ($response) {
        if ($errors = $response->getErrors()) {
            exit("Response was have errors: " . implode("\n", $errors) . "\n");
        }

        foreach ($response->getRows(10) as $rows) {
            print_r($rows);
        }
    }

## Contributions

Contributions are most welcomed, just fork modify and submit a pull request.

## Credits

- [Vladislav Lyshneko](https://github.com/vladdnepr)
- [All Contributors](../../contributors)

## License

The MIT License. Please see [License File](LICENSE.md) for more information.


