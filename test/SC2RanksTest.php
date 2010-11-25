<?php
/**
 * PHPUnit Tests
 * Run 'phpunit SC2RanksTest' on cmd line with php unit installed
 */
 
require('../sc2ranks.php');

$request = new sc2ranks_request("github.com/ameerkat/sc2ranks-php", "fopen");

class SC2RanksTest extends PHPUnit_Framework_TestCase
{	
	public function testGetCharacterData()
    {
		global $request;
		$data = $request->get_character_data("meerkat", 678);
		$this->assertTrue($request != null);
		$this->assertTrue($data != null);
		// We are assuming this does work
		$this->assertEquals($request->last_response()->name, "meerkat");
		$this->assertFalse(isset($request->last_response()->error));
		if($request->is_json_last_error_enabled())
			$this->assertEquals(JSON_ERROR_NONE);
		$data = $request->get_character_data("HuK", 530);
		$this->assertTrue($request != null);
		$this->assertTrue($data != null);
		// We are assuming this doesn't work
		$this->assertObjectHasAttribute('error', $request->last_response());
		if($request->is_json_last_error_enabled())
			$this->assertEquals(JSON_ERROR_NONE);
		// Check that the response get's cleared
		$data = $request->get_character_data("meerkat", 678);
		$this->assertTrue($request != null);
		$this->assertTrue($data != null);
		// We are assuming this does work
		$this->assertFalse(isset($request->last_response()->error));
		$this->assertEquals($request->last_response()->name, "meerkat");
		if($request->is_json_last_error_enabled())
			$this->assertEquals(JSON_ERROR_NONE);
			
		// We are assuming this doesn't work
		$data = $request->get_character_data("", 0);
		$this->assertTrue($request != null);
		$this->assertTrue($data != null);
		$this->assertObjectHasAttribute('error', $request->last_response());
		if($request->is_json_last_error_enabled())
			$this->assertEquals(JSON_ERROR_NONE);
		$data = $request->get_character_data("", -1);
		$this->assertTrue($request != null);
		$this->assertTrue($data != null);
		$this->assertObjectHasAttribute('error', $request->last_response());
		if($request->is_json_last_error_enabled())
			$this->assertEquals(JSON_ERROR_NONE);

		$data = $request->get_character_data("meerkat", 678);
		$this->assertTrue($request != null);
		$this->assertTrue($data != null);
		// We are assuming this does work
		$this->assertFalse(isset($request->last_response()->error));
		$this->assertEquals($request->last_response()->name, "meerkat");
		if($request->is_json_last_error_enabled())
			$this->assertEquals(JSON_ERROR_NONE);
		
		// THIS BREAKS THE PROGRAM
		// TODO Fix this
		// $data = $request->get_character_data("¾È³çÇÏ¼¼¿ä", 0);
		// $this->assertTrue($request != null);
		// $this->assertTrue($data != null);
		// $this->assertObjectHasAttribute('error', $request->last_response());
		
		$data = $request->get_character_data("_asd, fff%\ okay", 0);
		$this->assertTrue($request != null);
		$this->assertTrue($data != null);
		$this->assertObjectHasAttribute('error', $request->last_response());
		if($request->is_json_last_error_enabled())
			$this->assertEquals(JSON_ERROR_NONE);
    }
	
	public function testGetBracketData(){
		global $request;
		$data = $request->get_character_data("meerkat", 678);
		$this->assertTrue($request != null);
		$this->assertTrue($data != null);
		$record = $request->get_bracket_data();
		$record = $record[0];
		$this->assertTrue(isset($record) && $record != null);
		$this->assertObjectHasAttribute('division', $record);
		$this->assertObjectHasAttribute('points', $record);
		$this->assertObjectHasAttribute('division_rank', $record);
		$this->assertObjectHasAttribute('ratio', $record);
		$this->assertObjectHasAttribute('fav_race', $record);
		$this->assertObjectHasAttribute('league', $record);
		$this->assertObjectHasAttribute('wins', $record);
		$this->assertObjectHasAttribute('world_rank', $record);
		$this->assertObjectHasAttribute('losses', $record);
		$this->assertObjectHasAttribute('updated_at', $record);
		$this->assertObjectHasAttribute('is_random', $record);
		$this->assertObjectHasAttribute('bracket', $record);
		$this->assertObjectHasAttribute('region_rank', $record);
		$data_old = $data;
		
		$data = $request->get_character_data("HuK", 530);
		$this->assertTrue($request != null);
		$this->assertTrue($data != null);
		$this->assertObjectHasAttribute('error', $request->last_response());
		$record = $request->get_bracket_data();
		$this->assertNull($record);
		$record_old = $request->get_bracket_data($data_old);
		$this->assertTrue($record_old[0] != null);
	}
	
	public function testGetCharacterDataByBnet(){
		// TODO
	}
	
	public function testGetCharacterDataByProfile(){
		// TODO
	}
}
?>
