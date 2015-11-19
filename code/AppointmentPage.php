<?
	class BlockedAppointmentDate extends DataObject
	{
		private static $db = array( 
			"Title" => "Varchar(255)", 
			"Date" => "Varchar(255)" 
		);
		
		private static $has_one = array(
			"AppointmentPage" => "AppointmentPage"
		); 		
		
        public function getCMSFields()
        {
			$fields = parent::getCMSFields();
			$fields->replaceField('Date',DateField::create('Date')->setConfig('showcalendar',true));
			$this->extend('updateCMSFields',$fields);
			return $fields;
        }
		
		private static $summary_fields = array(
			"Title" => "Title",
			"Date" => "Date"
		);
		
		public function canCreate($member = null) { return true; }
		public function canDelete($member = null) { return true; }
		public function canEdit($member = null)   { return true; }
		public function canView($member = null)   { return true; }
	}
	
	class AppointmentPageSubmission extends FormPageSubmission
	{
		private static $db = array( 
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
		);
		
		private static $summary_fields = array(
			"Created.Nice" => "Date",
			"FirstName" => "First Name",
			"LastName" => "Last Name",
			"Email" => "Email Address",
			"Recipient" => "Recipient"
		);
		
		private static $default_sort = "Created DESC";	
		
		function getCMSFields()
		{
			$fields = parent::getCMSFields();
			$this->extend('updateCMSFields',$fields);
			return $fields;
		}
				
		public function canCreate($member = null) { return false; }
		public function canDelete($member = null) { return true; }
		public function canEdit($member = null)   { return false; }
		public function canView($member = null)   { return true; }
	}
	
	class AppointmentPage extends FormPage
	{
		
		private static $db = array(
			"BlockWeekends" => "Boolean",
			"OpenTime" => "Varchar(255)",
			"CloseTime" => "Varchar(255)",
			"AutoResponderSubject" => "Varchar(255)",
			"AutoResponder" => "HTMLText",
			'TimeStep' => 'Int'
		);

		private static $has_many = array(
			"BlockedAppointmentDates" => "BlockedAppointmentDate",
		);
		
		private static $defaults = array(
			'TimeStep' => '15'
		);
		
		public function getCMSFields()
		{
			$fields = parent::getCMSFields();
			
			$blocked_config = GridFieldConfig::create()->addComponents(				
				new GridFieldToolbarHeader(),
				new GridFieldAddNewButton('toolbar-header-right'),
				new GridFieldSortableHeader(),
				new GridFieldDataColumns(),
				new GridFieldPaginator(10),
				new GridFieldEditButton(),
				new GridFieldDeleteAction(),
				new GridFieldDetailForm()				
			);
			$fields->addFieldToTab('Root.AppointmentSettings', new GridField('BlockedAppointmentDates','Blocked Appointment Dates',$this->BlockedAppointmentDates(),$blocked_config));
			
			$fields->addFieldToTab("Root.AppointmentSettings", new CheckboxField("BlockWeekends", "Block Weekends?")); 
			$fields->addFieldToTab('Root.AppointmentSettings', new NumericField('TimeStep','Time Step (in time selection)') );
			$fields->addFieldToTab("Root.AppointmentSettings", new DropdownField("OpenTime", "Opening Time", $this->TimeArray())); 
			$fields->addFieldToTab("Root.AppointmentSettings", new DropdownField("CloseTime", "Closing Time", $this->TimeArray()));
			$fields->addFieldToTab("Root.AppointmentSettings", new TextField("AutoResponderSubject", "Auto-responder Subject")); 
			$fields->addFieldToTab("Root.AppointmentSettings", new HTMLEditorField("AutoResponder", "Auto-responder Body"));
			
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
			return $output;
		}	
		
	}
	
	class AppointmentPage_Controller extends FormPage_Controller
	{	
	
		public function FormFields()
		{
			$fields = array(
				"FirstName" => array(
					"FieldType" => "TextField",
					"Required" => true
				), 
				"LastName" => array(
					"FieldType" => "TextField",
					"Required" => true
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
	
		function FormConfig()
		{
			$config = array(
				'useNospam' => true
			);
			$this->extend('updateFormConfig',$config);
			return $config;
		}
		
		public function AllowedTimeArray(){
			$full = $this->TimeArray();
			$allowed = array();
			$open = strtotime($this->OpenTime);
			$close = strtotime($this->CloseTime);
			foreach($full as $key => $value){
				$i = strtotime($key);
				
				//Normal
				if($i >= $open && $i <= $close){
					$allowed[$key] = $value;
				}
				
				//Overnight
				if($open > $close){
					if($i <= $close || $i >= $open){
						$allowed[$key] = $value;
					}		
				}
			}
			return $allowed;
		}
	
		function PageCSS()
		{
			$CSS = array_merge(
				parent::PageCSS(),
				array(
					"iq-appointmentpage/javascript/jquery-ui.min.css",
					"iq-appointmentpage/javascript/jquery.ui.theme.css"
				)
			);
			$this->extend('updatePageCSS',$CSS);
			return $CSS;
		}
		
		function PageJS()
		{
			$JS = array_merge(
				parent::PageJS(),
				array(
					"iq-appointmentpage/javascript/jquery-ui.js"
				)
			);
			$this->extend('updatePageJS',$JS);
			return $JS;
		}
	
		function CustomJS()
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
			
			$(document).ready(function(){
				$('input.date').datepicker({
					minDate: 0,
					beforeShowDay: eval(checker)
				});
			});
			function blockedDays(date) {
				var m = date.getMonth(), d = date.getDate(), y = date.getFullYear();
				for (i = 0; i < disabledDays.length; i++) {
					if($.inArray((m+1) + '-' + d + '-' + y,disabledDays) != -1 || new Date() > date) {
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