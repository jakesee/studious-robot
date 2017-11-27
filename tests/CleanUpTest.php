<?php
namespace VUOX\Tests;

use PHPUnit\Framework\TestCase;

class CleanUp extends BaseTestCase
{
	// This is just to clean up the database
	// after all the tests have run
	public function testCleanUpDatabase()
	{
		$this->app = new \Application();

		// clean up database
        $DB = $this->app->db->getConnection();
        $tables = $DB->select('SHOW TABLES');
        foreach($tables as $table)
        {
            $DB->table($table->Tables_in_slim)->truncate();
        }
	}
}
