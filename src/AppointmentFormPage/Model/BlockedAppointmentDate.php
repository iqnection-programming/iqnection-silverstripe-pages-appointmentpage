<?php

namespace IQnection\AppointmentFormPage\Model;

use SilverStripe\ORM;
use SilverStripe\Forms;

class BlockedAppointmentDate extends ORM\DataObject
{
	private static $table_name = 'BlockedAppointmentDate';
	private static $singluar_name = 'Blocked Appointment Date';
	private static $plural_name = 'Blocked Appointment Dates';
	
	private static $db = [
		"Title" => "Varchar(255)", 
		"Date" => "Date" 
	];
	
	private static $has_one = [
		"AppointmentPage" => \IQnection\AppointmentFormPage\AppointmentFormPage::class
	];
	
	private static $summary_fields = [
		"Title" => "Title",
		"Date" => "Date"
	];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->removeByName('LinkTracking');
		$fields->removeByName('FileTracking');
		return $fields;
	}
	
	public function canCreate	($member = null, $context = array()) 	{ return true; }
	public function canDelete	($member = null, $context = array()) 	{ return true; }
	public function canEdit		($member = null, $context = array())   	{ return true; }
	public function canView		($member = null, $context = array())   	{ return true; }
	
	public function validate()
	{
		$result = parent::validate();
		if (!$this->Title) { $result->addFieldError('Title','Please title this date'); }
		if (!$this->Date) { $result->addFieldError('Date','Please set a date'); }
		return $result;
	}
}

