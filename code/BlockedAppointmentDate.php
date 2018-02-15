<?php

namespace IQnection\AppointmentFormPage;

use SilverStripe\ORM;
use SilverStripe\Forms;

class BlockedAppointmentDate extends ORM\DataObject
{
	private static $table_name = 'BlockedAppointmentDate';
	
	private static $db = [
		"Title" => "Varchar(255)", 
		"Date" => "Date" 
	];
	
	private static $has_one = [
		"AppointmentPage" => \AppointmentFormPage::class
	];
	
	private static $summary_fields = [
		"Title" => "Title",
		"Date" => "Date"
	];
	
	public function canCreate	($member = null, $context = array()) 	{ return true; }
	public function canDelete	($member = null, $context = array()) 	{ return true; }
	public function canEdit		($member = null, $context = array())   	{ return true; }
	public function canView		($member = null, $context = array())   	{ return true; }
}

