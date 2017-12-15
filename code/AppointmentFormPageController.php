<?php


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
				"Value" => "AllowedTimeArray"
			),
			"Date2" => array(
				"FieldType" => "DateField",
				"Label" => "Date (Second Choice)",
			),
			"Time2" => array(
				"FieldType" => "DropdownField",
				"Label" => "Time (Second Choice)",
				"ExtraClass" => "time_dropdown",
				"Value" => "AllowedTimeArray"
			),
			"Date3" => array(
				"FieldType" => "DateField",
				"Label" => "Date (Third Choice)",
			),
			"Time3" => array(
				"FieldType" => "DropdownField",
				"Label" => "Time (Third Choice)",
				"ExtraClass" => "time_dropdown",
				"Value" => "AllowedTimeArray"
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
		$checker = $this->BlockWeekends ? "noWeekendsOrBlocked" : "blockedDays";
		$blocks = "";
		$bt = count($this->BlockedAppointmentDates()->toArray());
		$i = 1;
		foreach($this->BlockedAppointmentDates()->toArray() as $blocked)
		{
			$blocks .= "'".date('n-j-Y' ,strtotime($blocked->Date))."'";
			if($i < $bt)$blocks .= ",";
			$i++;
		}
		$js .= "
		var checker = ".$checker.";
		var disabledDays = [".$blocks."];
		var disabledWeekdays = [".$this->BlockedWeekdays."];
		
		$(document).ready(function(){
			$('input.date').datepicker({
				minDate: 0,
				beforeShowDay: eval(checker)
			});
		});
		function blockedDays(date) {
			var m = date.getMonth(), d = date.getDate(), y = date.getFullYear(),wkdy = date.getDay();
			// check blocked dates
			for (i = 0; i < disabledDays.length; i++) {
				if($.inArray((m+1) + '-' + d + '-' + y,disabledDays) != -1 || $.inArray((wkdy+1),disabledWeekdays) != -1 || new Date() > date) {
					return [false];
				}
			}
			return [true];
		}
		
		function noWeekendsOrBlocked(date) {
			var noWeekend = jQuery.datepicker.noWeekends(date);
			return noWeekend[0] ? blockedDays(date) : noWeekend;
		}
		";
		$this->extend('updateCustomJS',$js);
		return $js;
	}
	
}



