<?php defined('SYSPATH') or die('No direct script access.');

/* ------------------------------------------------------

Helper - country_state

Author: Josh Turmel
Date: 2008-02-11
Description:
	This class returns countries and states from the DB

------------------------------------------------------ */
class country_state {
	
	public static function countries() {
		$db = new Database;
		return $db->from('countries')->get()->result_array(false);
	}
	
	public static function getCountryByISO($iso)
	{
		$db = new Database;
		return $db->from('countries')->where(array('iso' => $iso))->limit(1)->get()->current();
	}
	
	public static function states() {
		$db = new Database;
		return $db->from('states')->get()->result_array(false);
	}
}