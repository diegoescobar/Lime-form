<?php 
/*
 * Dynamic Form Builder
 * 
 * Uses Valitron validation ( https://github.com/vlucas/valitron )
 * Uses PHPMailer for emails ( https://github.com/PHPMailer/PHPMailer)
 * Uploads files
 * 
 * To Do:
 * 	- Add form sanitation via HTML Purifier (http://htmlpurifier.org/)
 *  - connect to API
 */

class lime_form {
	public $settings;
	public $fields;
	public $email;
	public $errors;
	public $spam;
	public $db;
	public $upload;
	public $db_data;
	public $values;
	

	/* 
	* variable constructors
	*/
	function __construct() {
		$this->upload_settings();
		if ($_REQUEST)	{
			$this->values();
		}

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
	
	public function values($array = array()){
		$this->values = $array;
		
		if ( strtoupper ( $this->settings['method'] ) == "GET"){
			$request = $_GET;
		}
		if ( strtoupper ( $this->settings['method'] ) == "POST"){
			$request = $_POST;
		} else {
			$request = $_REQUEST;
		}

		foreach ( $request AS $key=>$value ){
			$this->values[$key] = htmlentities(strip_tags($value));
		}
	}

	public function db( $array = array() ){
		$this->db_data  = $array;
	}

	public function upload_settings ( $array = array()){
		/* defaults */
		$this->upload['dir'] = __DIR__ . "/../uploads/";				
		$this->upload["size"] =  50000000;
		$this->upload['FileType'] = array( "jpg", "png", "jpeg", "gif" );
		
		/* new settings */
		if ( !empty( $array ['dir'] ) ) { $this->upload['dir'] = $array['dir']; }
		if ( !empty( $array ["size"] ) ) { $this->upload['size'] = $array['size'] ; }
		if ( !empty( $array ['FileType'] ) ) { $this->upload['FileType'] = $array['FileType'] ; }
	}

	/*************/
	
	/* 
	* Form Field generatore
	* adds errors and returns field values
	*/
	function generate_fields (  $array, $form ="" ){
		
		if (isset($array )){

			
			/*Check for errors*/
			if (!empty(  $this->values[$this->settings['name']]  )){
				//$this->errors =  $this->validate( $array, $this->values  );
				$this->errors();
			}

			foreach ($array AS $field ) {
				$req = "";
				if ( isset($field['required'])){
					 $req = "*";
				}
				$form .= '<div>';
				
				/*set label field*/
				if ( $field['type'] != "hidden"){
					$form .= '<div><label for="' . $this->field_namer ( $field[0] ) . '">'.$field[0]." ".$req."</label></div>";
				}
				
				/* if field has existing value, set value */
				if (isset($this->values[$this->field_namer ( $field[0])])){
					$value = $this->values[$this->field_namer ( $field[0])];
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
					case "file":
						$form .= '<div><input type="'.$field['type'].'" name="'.$this->field_namer ( $field[0]).'" id="'.$this->field_namer ( $field[0]).'" value="' . $value . '"></div>';
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

	/* 
	* Form Header Builder
	*/
	function from_header ( $array ){
		foreach ( $array  As $key=>$value ) {		
			$settings[] = $key .'="'.$value.'" ';
		}

		if ($this->searchMultiArray('type', 'file', $this->fields)){ $settings[] = 'enctype="multipart/form-data"'; }		

		$form = '<div class="' . $this->settings['name'] . '">';
		$form .= "<form " .  implode(' ', $settings ) . '>';
				
		return $form;
	}
	
	/* 
	* Form Footer Builder
	*/
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
	
	/* 
	* Main Form Builder function
	*/
	function form ( $array = array() ){
		$form = "";
		
		/*if (isset($this->errors) ){
			echo "<pre>";
			var_dump (  $this->errors );
		}*/

		if (isset($this->errors) && $this->errors == false ) {
			if ($this->errors == false && !empty($this->email)) {
				if ( !empty( $this->email ) ) {
					$this->send_email();
				}
			}

			if ( !empty( $this->db ) && !empty( $this->db_data )) {
				$this->db_insert();
			}
			if (!$this->file_upload_validate()){
				$this->file_upload();
			}
			
			$form = "<div><h2>" . __("Form Successfully Submited") . "</h2></div>";

		}else{
			$form = $this->from_header ( $this->settings );
			$form .= $this->generate_fields(  $this->fields );
			$form .= $this->form_footer();
		}
		
		return $form;
	}

	/* 
	* Error Handling 
	*/
	function errors(){
		if (!empty(  $this->values[$this->settings['name']]  )){
			$this->errors =  $this->validate( $this->fields, $this->values  );
		}

		if ($this->searchMultiArray('type', 'file', $this->fields)){
			if ($this->file_upload_validate()){
				$this->errors['file'] = $this->file_upload_validate();  
			}
		}

		if (!empty(  $this->values['name']) && $this->values['name'] == $this->settings['name']  ){
			if ($this->settings['ajax'] == true){
				if ($this->errors){
					echo json_encode($this->errors);
				}
				exit;
			}
		}
	}

	/* 
	* Form Field Validator
	* Uses Valitron validation
	*/
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
	
	/*  
	* Populates the email field
	*/
	function email($email = array()){
		if (is_array( $email['settings']['addAddress'] )){
			$this->email['addAddress'] = implode(',', $email['settings']['addAddress']);     // Add a recipient
		}else{
			$this->email['addAddress'] = $email['settings']['addAddress'];     // Add a recipient
		}
		if (isset($email['settings']['addReplyTo']))
			$this->email['addReplyTo'] = implode(',', $email['settings']['addReplyTo']);
		
		if (isset($email['settings']['addCC']))
			$this->email['addCC'] = implode(',', $email['settings']['addCC']);
		
		if (isset($email['settings']['addBCC']))
			$this->email['addBCC'] = implode(',', $email['settings']['addBCC']);
		
		$this->email['Subject'] = $email['settings']['Subject'];

		include_once (  __DIR__ . '/email_template.php' );
		
		$email_arr["logo"] = "";
		$email_arr["content"] = "";
		$email_arr["contact_info"] = "";
		$this->email['Body']    = email_template ( $email_arr );
		$this->email['AltBody'] = 'This is the body in plain text for non-HTML mail clients';
		
		/*if ($attachment){
		 $this->email['addAttachment'] = array('/var/tmp/file.tar.gz');// Add attachments
		 }*/

		/* Set variables */
		foreach ( $email['variables'] AS $key=>$fields){
			if ( is_array( $fields )) {
				foreach ($fields AS $field){
					$field_arr[] = $this->values[ $this->field_namer( $field ) ];
				}
				$value = implode(' ', $field_arr);
			}else{
 				if (isset( $this->values[ $this->field_namer( $fields )] )){
					$value = $this->values[ $this->field_namer( $fields )];
				}
			}
			if (!empty($value) && isset( $key )){
				$this->email[ $key ] =  $value;
			}
		}

	}

	function email_body ( $array){
		$email_content = '';
		/*set label field*/
		
		if (isset( $array )){
			$email_content .= '<table style="width:100%;">';
			
			foreach ($array AS $field ) {
				$email_content .= '<tr>';
				/* if field has existing value, set value */
				if (isset($this->values[$this->field_namer ( $field[0])])){
					$value = $this->values[$this->field_namer ( $field[0])];
				}else if (!empty($field['value'])){
					$value = $field['value'];
				}
				else{
					$value = "";
				}
				switch ($field['type']) {
					case "file":
						//$email_content .= '<div><input type="'.$field['type'].'" name="'.$this->field_namer ( $field[0]).'" id="'.$this->field_namer ( $field[0]).'" value="' . $value . '"></div>';
						//file attachment needs to be loaded
					break;
					case "hidden":
						$email_content .= '';
						break;
					default:
						$email_content .= '<td>'.$field[0].'</td><td>' . $value . "</td>";
					break;	
				}
				$email_content .= "</tr>";
			}
			$email_content .= "</table>";
		} else {
			$email_content = false;
		}
		
		return $email_content;
	}

	/* 
	* Builds and sends email
	*/
	function send_email(){
		require __DIR__ . '/PHPMailer/PHPMailerAutoload.php';
		
		$mail = new PHPMailer;

		$mail->From = $this->email['email'];
		$mail->FromName = $this->email['name'];
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

	/*  
	* Database Connection
	*/

	function db_connect( $db ){
		$this->db = $db;
	}

	/* 
	* Insert entry into database  
	*/
	function db_insert ( $array = array() ){
		foreach ( $this->db_data['fields'] AS $key=>$value){
			if (is_array( $value )){ $value = serialize( $value ); }
			$query = "INSERT INTO " . $this->db_data['table'] . ' ( ' . $key . ' ) VALUES ( "' . addslashes( $value ) . '" )';
		}

		if (!mysqli_query($this->db, $query)) {
			$this->errors['alert'] = "Error description: " . mysqli_error( $this->db );
		}
	}

	/* 
	* Validate uploaded file
	*/
	function file_upload_validate( ){
		$upload = true;
		$file_errors = "";
		//$target_dir = __DIR__ . "/development/lime-form/uploads/";
		$target_dir = $this->upload['dir'];
		//echo $target_dir;

		if (isset($_FILES) && !empty($_FILES)){

			foreach ($_FILES AS $key=>$file){
				$target_file = $target_dir . '/' . date('dmy') . basename( $file["name"] );
				$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

				// Check if image file is a actual image or fake image
				if(isset($file) && !empty($file["tmp_name"])) {
				    $check = getimagesize($file["tmp_name"]);
				    if($check !== false) {
				        $upload = true;
				    } else {
				        $file_errors[] = "File is not an image.";
				        $upload = false;
				    }
				}

				// Check if file already exists
				if (file_exists($target_file)) {
				    $file_errors[] = "Sorry, file already exists.";
				    $upload = false;
				}

				// Check file size
				if ($file["size"] > $this->upload["size"]) {
				    $file_errors[] = "Sorry, your file is too large.";
				    $upload = false;
				}

				// Allow certain file formats
				if(!in_array($imageFileType, $this->upload['FileType'])  ) {
				    $file_errors[] = "This filetype is not allowed.";
				    $upload = false;
				}
				$this->values[ $key ] = $target_file;
			}
		}else{ 
			$upload = false; 
		}
		if ( $upload == false ) { return $file_errors;  }
	}

	/*  
	* Upload Subitted File
	*/
	function file_upload( ){
		$target_dir = $this->upload['dir'];
		foreach ($_FILES AS $file){
			$target_file = $target_dir . basename($file["name"]);
			$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		    if (!move_uploaded_file($file["tmp_name"], $target_file)) {
		        $this->errors[] = "Sorry, there was an error uploading your file.";
		        echo "Sorry, there was an error uploading your file.";
		    }
		}
	}
	
	/* 
	* Builds spam protection
	* - Currently only uses timestamp check and honeypot methods
	*/
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
	

	/* 
	* Future API connection
	*/
	function api(){
		return false;
	}

	/* 
	* Utility: form name nicifier; parses field name into a more usable form 
	*/
	function field_namer ( $string ){
		return str_replace(' ', '_', $string);
	}

	/* 
	* Utility: Multidimensional array search 
	* pretty damn handy
	*/
	function searchMultiArray($a_key, $id, $array) {
		foreach ($array as $key => $val) {
			if ($val[$a_key] === $id) {
				return $key;
			}
		}
		return null;
	}

}