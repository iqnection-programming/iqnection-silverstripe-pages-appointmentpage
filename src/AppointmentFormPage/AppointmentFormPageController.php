<?php

namespace IQnection\AppointmentFormPage;

use IQnection\FormPage\FormPageController;

class AppointmentFormPageController extends FormPageController
{	
	public function FormFields()
	{
		$fields = array(
			"FirstName" => array(
				"FieldType" => "TextField",
				"Required" => true,
				'Group' => 'Name'
			), 
			"LastName" => array(
				"FieldType" => "TextField",
				"Required" => true,
				'Group' => 'Name'
			), 
			"Email" => array(
				"FieldType" => "EmailField",
				"Required" => true
			), 
			"Phone" => array(
				"FieldType" => "TextField"
			), 
			"Recipient" => $this->RecipientFieldConfig(),
			"Date1" => array(
				"FieldType" => "DateField",
				"Label" => "Date (First Choice)",
				"Required" => true,
			),
			"Time1" => array(
				"FieldType" => "DropdownField",
				"Label" => "Time (First Choice)",
				"Required" => true,
				"ExtraClass" => "time_dropdown",
				"Value" => "AllowedTimeArray",
				'EmptyString' => '-- Select --'
			),
			"Date2" => array(
				"FieldType" => "DateField",
				"Label" => "Date (Second Choice)",
			),
			"Time2" => array(
				"FieldType" => "DropdownField",
				"Label" => "Time (Second Choice)",
				"ExtraClass" => "time_dropdown",
				"Value" => "AllowedTimeArray",
				'EmptyString' => '-- Select --'
			),
			"Date3" => array(
				"FieldType" => "DateField",
				"Label" => "Date (Third Choice)",
			),
			"Time3" => array(
				"FieldType" => "DropdownField",
				"Label" => "Time (Third Choice)",
				"ExtraClass" => "time_dropdown",
				"Value" => "AllowedTimeArray",
				'EmptyString' => '-- Select --'
			),
			"Comments" => array(
				"FieldType" => "TextAreaField"
			)
		);
		$this->extend('updateFormFields',$fields);
		return $fields;
	}

	public function FormConfig()
	{
		$config = array(
			'useNospam' => true
		);
		$this->extend('updateFormConfig',$config);
		return $config;
	}
	
	public function AllowedTimeArray()
	{
		$full = $this->TimeArray();
		$allowed = array();
		$open = strtotime($this->OpenTime);
		$close = strtotime($this->CloseTime);
		foreach($full as $key => $value)
		{
			$i = strtotime($key);
			
			//Normal
			if($i >= $open && $i <= $close)
			{
				$allowed[$key] = $value;
			}
			
			//Overnight
			if($open > $close)
			{
				if($i <= $close || $i >= $open)
				{
					$allowed[$key] = $value;
				}		
			}
		}
		return $allowed;
	}

	public function PageCSS()
	{
		$CSS = array_merge(
			parent::PageCSS(),
			array(
				"javascript/jquery-ui.min.css",
				"javascript/jquery.ui.theme.css"
			)
		);
		return $CSS;
	}
	
	public function PageJS()
	{
		$JS = array_merge(
			parent::PageJS(),
			array(
				"javascript/jquery-ui.js"
			)
		);
		return $JS;
	}

	public function CustomJS()
	{
		$js = parent::CustomJS();
		$blocks = [];
		foreach($this->BlockedAppointmentDates() as $blocked)
		{
			$blocks[] = date('n-j-Y' ,strtotime($blocked->Date));
		}
		$js .= 
"window._apptPage = window._apptPage || {};
window._apptPage.blockWeekends = ".($this->BlockWeekends ? "true" : 'false').";
window._apptPage.disabledDays = ".json_encode($blocks).";
window._apptPage.disabledWeekdays = [".$this->BlockedWeekdays."];";
		$this->extend('updateCustomJS',$js);
		return $js;
	}
	
}



