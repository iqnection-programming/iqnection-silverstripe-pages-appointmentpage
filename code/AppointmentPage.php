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
			$date = new DateField("Date", "Date:");
			$date->setConfig('showcalendar',true);
			return new FieldList(
				new TextField("Title", "Title:"),
				$date
			);
        }
		
		private static $summary_fields = array(
			"Title" => "Title",
			"Date" => "Date"
		);
	}
	
	class AppointmentFormSubmission extends DataObject
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
			"Created" => "Date",
			"FirstName" => "First Name",
			"LastName" => "Last Name",
			"Email" => "Email Address",
			"Recipient" => "Recipient"
		);
		
		private static $default_sort = "Created DESC";	
	}
	
	class AppointmentPage extends FormPage
	{
		
		public $submission_class = "AppointmentFormSubmission";
		
		static $db = array(
			"BlockWeekends" => "Boolean",
			"OpenTime" => "Varchar(255)",
			"CloseTime" => "Varchar(255)",
			"AutoResponderSubject" => "Varchar(255)",
			"AutoResponder" => "HTMLText"
		);

		static $has_many = array(
			"BlockedAppointmentDates" => "BlockedAppointmentDate",
			"AppointmentFormRecipients" => "AppointmentFormRecipient"
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
			$fields->addFieldToTab('Root.Content.AppointmentSettings', new GridField('BlockedAppointmentDates','Blocked Appointment Dates',$this->BlockedAppointmentDates(),$blocked_config));
			
			$fields->addFieldToTab("Root.Content.AppointmentSettings", new CheckboxField("BlockWeekends", "Block Weekends?")); 
			$fields->addFieldToTab("Root.Content.AppointmentSettings", new DropdownField("OpenTime", "Opening Time", $this->TimeArray())); 
			$fields->addFieldToTab("Root.Content.AppointmentSettings", new DropdownField("CloseTime", "Closing Time", $this->TimeArray()));
			$fields->addFieldToTab("Root.Content.AppointmentSettings", new TextField("AutoResponderSubject", "Auto-responder Subject")); 
			$fields->addFieldToTab("Root.Content.AppointmentSettings", new HTMLEditorField("AutoResponder", "Auto-responder Body"));

			return $fields;
		}
		
		public function TimeArray(){
			$output = array();
			for($h = 12,$k = 1; $k<3; $h++){
				for($m=0;$m<=45;$m+=15){
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
			$dir = ViewableData::ThemeDir();
			return array_merge(
				parent::PageCSS(),
				array(
					$dir."/javascript/jquery-ui.min.css",
					$dir."/javascript/jquery.ui.theme.css"
				)
			);
		}
		
		function PageJS()
		{
			$dir = ViewableData::ThemeDir();
			return array_merge(
				parent::PageJS(),
				array(
					$dir."/javascript/jquery-ui.js"
				)
			);
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
			return $js;
		}
		
		public $form_fields = array(
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
			"Recipient" => array(
				"FieldType" => "DropdownField",
				"Value" => "FindRecipients"
			),
			"Date1" => array(
				"FieldType" => "DateField",
				"Label" => "Date (First Choice)",
				"Required" => true,
			),
			"Time1" => array(
				"FieldType" => "DropdownField",
				"Label" => "Time (First Choice)",
				"Required" => true,
				"ExtraClass" => "time",
				"Value" => "AllowedTimeArray"
			),
			"Date2" => array(
				"FieldType" => "DateField",
				"Label" => "Date (Second Choice)",
			),
			"Time2" => array(
				"FieldType" => "DropdownField",
				"Label" => "Time (Second Choice)",
				"ExtraClass" => "time",
				"Value" => "AllowedTimeArray"
			),
			"Date3" => array(
				"FieldType" => "DateField",
				"Label" => "Date (Third Choice)",
			),
			"Time3" => array(
				"FieldType" => "DropdownField",
				"Label" => "Time (Third Choice)",
				"ExtraClass" => "time",
				"Value" => "AllowedTimeArray"
			),
			"Comments" => array(
				"FieldType" => "TextAreaField"
			)
		);
		
	}