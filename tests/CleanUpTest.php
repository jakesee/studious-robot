<?php
namespace VUOX\Tests;

use PHPUnit\Framework\TestCase;

class CleanUp extends BaseTestCase
{
	// This is just to clean up the database
	// after all the tests have run
	public function testCleanUpDatabase()
	{
		self::$app = new \Application();

		// clean up database
        $DB = self::$app->db->getConnection();
        $tables = $DB->select('SHOW TABLES');
        foreach($tables as $table)
        {
            $DB->table($table->Tables_in_slim)->truncate();
        }
	}
}