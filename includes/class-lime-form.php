<?php 
/*
 * Dynamic Form Builder
 * 
 * Uses Valitron validation ( https://github.com/vlucas/valitron )
 * Uses PHPMailer for emails ( https://github.com/PHPMailer/PHPMailer)
 * 
 * To Do:
 * 	- Add form sanitation via HTML Purifier (http://htmlpurifier.org/)
 *  - connect to database
 *  - connect to API
 */

class lime_form {
	public $settings;
	public $fields;
	public $email;
	public $errors;
	public $spam;
	public $db;
	public $db_data;
	
	function __construct() {
		if ($this->errors){
			exit;
		}
	}
	
	public function settings($array = array()){
		$this->settings = $array;
	}
	
	public function fields($array = array()){
		$this->fields = $array;
		$this->errors();
	}
	
	public function db( $array = array() ){
		$this->db_data  = $array;
	}
	
	function spam (	$array = array() ) {
	
		foreach ($array AS $spam ){
			switch ($spam ){
				case 'honeypot':
					$this->spam .= '<input type="text" id="phone" class="">';
				break;
				case 'date':
					$this->spam .= '<input type="hidden" readonly="readonly" id="datetime" value="'. time() .'"class="">';
					break;
				default:
					$this->spam .= '';
				break;
			}
		}
	
	}
	
	function generate_fields (  $array, $form ="" ){
		
		if (isset($array )){

			if (!empty(  $_REQUEST[$this->settings['name']]  )){
				$this->errors =  $this->validate( $array, $_REQUEST  );
			}
			
			foreach ($array AS $field ) {
				$req = "";
				if ( isset($field['required'])){
					 $req = "*";
				}
				$form .= "<div>";
				if ( $field['type'] != "hidden"){
					$form .= "<div><label>".$field[0]." ".$req."</label></div>";
				}
				if (isset($_REQUEST[$this->field_namer ( $field[0])])){
					$value = $_REQUEST[$this->field_namer ( $field[0])];
				}else if (!empty($field['value'])){
					$value = $field['value'];
				}
				else{
					$value = "";
				}
				switch ($field['type']) {
					case "textarea":
						$form .= '<div><'.$field['type'].' id="'.$this->field_namer ( $field[0]).'" name='.$this->field_namer ( $field[0]).">" . $value . "</textarea></div>";
					break;
					case "select":
						//
						$form .= '<div><select name="'.$this->field_namer ( $field[0]).'" id="'.$this->field_namer ( $field[0]).'">';
						$sel = "";
						foreach($field['options'] AS $option){
							if ($value == $option){ $sel = " selected"; }else{$sel = "";}
							$form .= '<option value="'. $option .'"'. $sel .'>'.$option.'</option>';
							
						}
						$form .= '</select></div>';
					break;
					case "select":
						$form .= '<div><select name="'.$this->field_namer ( $field[0]).'" id="'.$this->field_namer ( $field[0]).'">';
						$sel = "";
						foreach($field['options'] AS $option){
							if ($value == $option){ $sel = " selected"; }else{$sel = "";}
							$form .= '<option value="'. $option .'"'. $sel .'>'.$option.'</option>';
								
						}
						$form .= '</select></div>';
					break;
					case "radio":
						$form .= '<div>';
						$sel = "";
						foreach($field['options'] AS $option){
							if ($value == $option){ $sel = " checked"; }else{$sel = "";}
							$form .= '<span><input type="radio" name="'.$this->field_namer ( $field[0]).'" id="'.$this->field_namer ( $field[0] . ' ' . $option ).'" value="'.$option.'"'. $sel .'>';
							$form .= '<label for="'.$this->field_namer ( $field[0] . ' ' . $option ).'">'.$option.'</label></span>';
						}
						$form .= '</div>';
					break;
					case "hidden":
						$form .= '<div><input type="'.$field['type'].'" name="'.$this->field_namer ( $field[0]).'" id="'.$this->field_namer ( $field[0]).'" value="' . $value . '"></div>';
					break;
					case "text":
						$form .= '<div><input type="'.$field['type'].'" name="'.$this->field_namer ( $field[0]).'" id="'.$this->field_namer ( $field[0]).'" value="' . $value . '"></div>';
					break;
					case "datepicker":
						$form .= '<div><input type="text" name="'.$this->field_namer ( $field[0]).'" id="'.$this->field_namer ( $field[0]).'" class="datepicker" value="' . $value . '"></div>';
					break;
					default:
						$form .= '<div><input type="'.$field['type'].'" name="'.$this->field_namer ( $field[0]).'" id="'.$this->field_namer ( $field[0]).'" value="' . $value . '"></div>';
					break;
				}
				
				if (isset($this->errors[$this->field_namer ( $field[0])])){
					
					$form .= '<span class="error">' . $this->errors[$this->field_namer ( $field[0])][0] . '</span>';
				}
				
				$form .= "</div>";
			}
		} else {
			$form = "Form Fields not defined.";
		}
				
		return $form;
	}
	
	function from_header ( $array ){
		foreach ( $array  As $key=>$value ) {		
			$settings[] = $key .'="'.$value.'" ';
		}
		
		$form = "<div>";
		$form .= "<form " .  implode(' ', $settings ) . '>';
				
		return $form;
	}
	
	function form_footer (){
		$form = "<div>";
		$form .= "<div>"."&nbsp;"."</div>";
		

		
		$form .= "<div>".'<input type="submit" name="'.	$this->settings['name'].'" value="Submit">';
		$form .= "</div>";
		$form .= "</div>";
		$form .= "</form>";
		$form .= "</div>";
		
		return $form;
	}
	
	function errors(){
		if (!empty(  $_REQUEST[$this->settings['name']]  )){
			$this->errors =  $this->validate( $this->fields, $_REQUEST  );
		}
		
		if (!empty(  $_REQUEST['name']) && $_REQUEST['name'] == $this->settings['name']  ){
			if ($this->settings['ajax'] == true){
				$this->errors =  $this->validate( $this->fields, $_REQUEST  );
				
				if ($this->errors){
					echo json_encode($this->errors);
				}
				exit;
			}
		}
	}

	function form ( $array = array() ){
		$form = "";
		if (isset($this->errors) && $this->errors == false ) {
		
			if ($this->errors == false && !empty($this->email)) {
				include_once ( 'includes/email_template.php' );
				
				$email_arr["logo"] = "";
				$email_arr["content"] = "";
				$email_arr["contact_info"] = "";
				$this->email['Body']    = email_template ( $email_arr );
				$this->email['AltBody'] = 'This is the body in plain text for non-HTML mail clients';
				
				/*if ($attachment){
				 $this->email['addAttachment'] = array('/var/tmp/file.tar.gz');// Add attachments
				 }*/

				if ( !empty( $this->email ) ) {
					$this->send_email();
				}
				if ( !empty( $this->db ) && !empty( $this->db_data )) {
					$this->db_insert();
				}
			}

			$form = "<div><h2>Form Successfully Submited</h2></div>";
		
		}else{
			$form = $this->from_header ( $this->settings );
			$form .= $this->generate_fields(  $this->fields );
			$form .= $this->form_footer();
		}
		
		return $form;
	}

	function validate ( $fields, $POST ){

		$v = new Validator($POST);
		
		foreach ($this->fields  AS $field){			
			if ( isset($field['required'])){
				if ($field['required'] == true){ $field['required'] = 'required'; }
				$v->rule($field['required'], $this->field_namer ( $field[0]));
			}
		}

		if($v->validate()) {
			return false;
		} else {
			return $v->errors();
		}
	}
	
	function email($email = array()){
		if (is_array( $email['addAddress'] )){
			$this->email['addAddress'] = implode(',', $email['addAddress']);     // Add a recipient
		}else{
			$this->email['addAddress'] = $email['addAddress'];     // Add a recipient
		}
		if (isset($email['addReplyTo']))
			$this->email['addReplyTo'] = implode(',', $email['addReplyTo']);
		
		if (isset($email['addCC']))
			$this->email['addCC'] = implode(',', $email['addCC']);
		
		if (isset($email['addBCC']))
			$this->email['addBCC'] = implode(',', $email['addBCC']);
		
		$this->email['Subject'] = $email['Subject'];
	}
	
	function send_email(){
		
		require 'includes/PHPMailer/PHPMailerAutoload.php';
		
		$mail = new PHPMailer;
		
		$mail->From = $_REQUEST['email'];
		$mail->FromName = $_REQUEST['first_name'] .' ' . $_REQUEST['last_name'];
		$mail->addAddress($this->email['addAddress']);     // Add a recipient
		$mail->addReplyTo($this->email['addReplyTo'] );
		
		if (isset($this->email['addCC']))
			$mail->addCC($this->email['addCC']);
		
		if (isset($this->email['addBCC']))
			$mail->addBCC( $this->email['addBCC'] );
		
		if (isset( $this->email['addAttachment'] ))
			$mail->addAttachment( $this->email['addAttachment'] );         // Add attachments
		//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		
		$mail->isHTML(true);                                  // Set email format to HTML
		
		$mail->Subject = $this->email['Subject'];
		$mail->Body    = $this->email['Body'];
		$mail->AltBody = $this->email['AltBody'];
		
		if(!$mail->send()) {
			$this->errors['email'] = 'Message could not be sent.';
			$this->errors['email_info'] = $mail->ErrorInfo;
		}
	}

	function db_connect( $db ){
		$this->db = $db;
	}

	function db_insert ( $array = array() ){
		foreach ( $this->db_data['fields'] AS $key=>$value){			
			$query = "INSERT INTO " . $this->db_data['table'] . ' ( ' . $key . ' ) VALUES ( "' . addslashes(serialize( $value )). '" )';
		}
		if (!mysqli_query($this->db, $query)) {
			$this->errors['alert'] = "Error description: " . mysqli_error( $this->db );
		}
	}
	
	function api(){}
	
	function field_namer ( $string ){
		return str_replace(' ', '_', $string);
	}
}