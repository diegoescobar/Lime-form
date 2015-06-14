<?php 
include_once ( 'includes/Validator.php' );
include_once ( 'includes/class-lime-form.php' ); 
$db = mysqli_connect("localhost","root","","development") or die("Error " . mysqli_error($link)); 
$attr	=	array (
		'settings'	=>  array(
				'id'	=> 'test',
				'name'  => "test",
				'action' => "#",
				'method' => "post",
				//'ajax'	=> true 	
			),

		'fields' => array(
				array('first name', 'type' => 'text', 'required' => 'alpha'),
				array('last name', 'type' => 'text' ),
				array('email', 'type' => 'text', 'required' => 'email' ),
				array('comment', 'type' => 'textarea', 'required' => true ),
				array('province', 'type' => 'select', 'options' => array('Ontario', 'Quebec', 'Nova Scotia') ),
				array('provinces', 'type' => 'radio', 'options' => array('Ontario', 'Quebec', 'Nova Scotia') ),
				array('datestamp', 'type' => 'hidden', 'value' => date('Y m d') ),
				array('date', 'type' => 'datepicker')
		) );

$email = array(
			'addAddress' => 'test@fakeemail.com',
			'addReplyTo' => array('info@example.com', 'Information'),
			//'addBCC' => array ('cc@example.com' ),
			//'addBCC' => array('bcc@example.com' ),
			'Subject' => 'Test Submission Like Whoa' );

$test = new lime_form();

//$test->email	( 	$email  );
$test->settings	(   $attr['settings'] );
$test->fields	(	$attr['fields'] );
$test->db_connect( 	$db ); 
$test->spam		(	array('honeypot', 'date') );
$test->db		(	array('table' => 'form',
				 	 	  'fields' => array( 'data' => array( 'first name','last name','email','comment' ))
					)
				);
