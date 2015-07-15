<?php


class ImportTest extends \PHPUnit_Framework_TestCase {

	public function testImportEmptyFile() {
		$file = '';
		$import = new \OCA\ContactsPlus\Import($file);
		$count = $import->getCount();
		$this->assertEquals(0, $count);
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionMessage No user id set
	 */
	public function testImportNoUserId() {
		$file = '';
		$import = new \OCA\ContactsPlus\Import($file);
		$import->import();
	}

}