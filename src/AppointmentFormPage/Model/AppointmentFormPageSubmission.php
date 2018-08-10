<?php

namespace IQnection\AppointmentFormPage\Model;

use IQnection\FormPage\Model\FormPageSubmission;

class AppointmentFormPageSubmission extends FormPageSubmission
{
	private static $table_name = 'AppointmentFormPageSubmission';
	
	private static $db = [ 
		"FirstName" => "Varchar(255)", 
		"LastName" => "Varchar(255)", 
		"Email" => "Varchar(255)", 
		"Phone" => "Varchar(255)", 
		"Recipient" => "Varchar(255)",
		"Date1" => "Varchar(255)",
		"Date2" => "Varchar(255)",
		"Date3" => "Varchar(255)",
		"Time1" => "Varchar(255)",
		"Time2" => "Varchar(255)",
		"Time3" => "Varchar(255)",
		"Comments" => "Text"
	];
	
	private static $summary_fields = [
		"Created.Nice" => "Date",
		"FirstName" => "First Name",
		"LastName" => "Last Name",
		"Email" => "Email Address",
		"Recipient" => "Recipient"
	];
	
	private static $export_fields = [
		"Created" => 'Date',
		"FirstName" => "First Name", 
		"LastName" => "Last Name", 
		"Email" => "Email", 
		"Phone" => "Phone", 
		"Recipient" => "Recipient",
		"Date1" => "Date 1",
		"Time1" => "Time 1",
		"Date2" => "Date 2",
		"Time2" => "Time 2",
		"Date3" => "Date 3",
		"Time3" => "Time 3",
		"Comments" => "Comments"
	];
	
	private static $default_sort = "Created DESC";	
			
	public function canCreate	($member = null, $context = array()) 	{ return false; }
	public function canDelete	($member = null, $context = array()) 	{ return true; }
	public function canEdit		($member = null, $context = array())  	{ return false; }
	public function canView		($member = null, $context = array())   	{ return true; }
}

