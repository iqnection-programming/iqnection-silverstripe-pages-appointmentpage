<?php

namespace IQnection\AppointmentFormPage;

use IQnection\FormPage\FormPage;
use SilverStripe\Forms;

class AppointmentFormPage extends FormPage
{
	private static $table_name = 'AppointmentFormPage';
	
	private static $icon = "iqnection-pages/appointmentformpage:images/icons/appointmentformpage-icon.png";
	
	private static $db = [
		"BlockWeekends" => "Boolean",
		'BlockedWeekdays' => 'Text',
		"OpenTime" => "Varchar(255)",
		"CloseTime" => "Varchar(255)",
		"AutoResponderFromEmail" => "Varchar(255)",
		"AutoResponderSubject" => "Varchar(255)",
		"AutoResponder" => "HTMLText",
		"AutoResponderIncludeSubmission" => "Boolean",
		'TimeStep' => 'Int'
	];

	private static $has_many = [
		"BlockedAppointmentDates" => \IQnection\AppointmentFormPage\Model\BlockedAppointmentDate::class,
		"AppointmentFormPageSubmissions" => \IQnection\AppointmentFormPage\Model\AppointmentFormPageSubmission::class
	];
	
	private static $defaults = [
		'TimeStep' => '15'
	];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		
		$fields->addFieldToTab('Root.AppointmentSettings', Forms\GridField\GridField::create(
			'BlockedAppointmentDates',
			'Blocked Appointment Dates',
			$this->BlockedAppointmentDates(),
			Forms\GridField\GridFieldConfig_RecordEditor::create()
		));
		
		$fields->addFieldToTab("Root.AppointmentSettings", Forms\CheckboxField::create("BlockWeekends", "Block Weekends?")); 
		$fields->addFieldToTab("Root.AppointmentSettings", Forms\CheckboxSetField::create("BlockedWeekdays", "Block Weekdays",array(1=>'Sunday',2=>'Monday',3=>'Tuesday',4=>'Wednesday',5=>'Thursday',6=>'Friday',7=>'Saturday'))); 
		$fields->addFieldToTab('Root.AppointmentSettings', Forms\NumericField::create('TimeStep','Time Step (in time selection)') );
		$fields->addFieldToTab("Root.AppointmentSettings", Forms\DropdownField::create("OpenTime", "Opening Time", $this->TimeArray())); 
		$fields->addFieldToTab("Root.AppointmentSettings", Forms\DropdownField::create("CloseTime", "Closing Time", $this->TimeArray()));
		$fields->addFieldToTab("Root.AppointmentSettings", Forms\TextField::create("AutoResponderFromEmail", "Auto-responder From Email")); 
		$fields->addFieldToTab("Root.AppointmentSettings", Forms\TextField::create("AutoResponderSubject", "Auto-responder Subject"));
		$fields->addFieldToTab("Root.AppointmentSettings", Forms\CheckboxField::create("AutoResponderIncludeSubmission", "Include Submitted Data in Email"));  
		$fields->addFieldToTab("Root.AppointmentSettings", Forms\HTMLEditor\HTMLEditorField::create("AutoResponder", "Auto-responder Body"));
		
		$this->extend('updateCMSFields',$fields);
		return $fields;

	}
	
	public function TimeArray()
	{
		$output = array();
		for($h = 12,$k = 1; $k<=2; $h++)
		{
			$TimeStep = ($this->TimeStep) ? $this->TimeStep : 15;
			$maxMinute = 60 - $TimeStep;				
			for($m=0;$m<=$maxMinute;$m+=$TimeStep)
			{
				$m = $m == 0 ? "00" : $m;
				$section = $k == 1 ? "AM" : "PM";
				$time = $h.":".$m." ".$section;
				$output[$time] = $time;	
			}
			$k = $h == 11 ? $k+1 : $k;
			$h = $h == 12 ? 0 : $h;
		}
		$this->extend('updateTimeArray',$output);
		return $output;
	}	
	
}

