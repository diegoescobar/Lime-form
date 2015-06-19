<?php 

include ( "includes/functions.php" );


//Set Form Settings
$attr	=	array (
		'settings'	=>  array(
				'id'	=> 'test',
				'name'  => "test",
				'action' => "#",
				'method' => "post",
				'ajax'	=> true
		),
		
//Set Form Fields and settings
		'fields' => array(
				array('first name', 'type' => 'text', 'required' => 'alpha'),
				array('last name', 'type' => 'text' ),
				array('email', 'type' => 'text', 'required' => 'email' ),
				array('comment', 'type' => 'textarea', 'required' => true ),
				array('province', 'type' => 'select', 'options' => array('Ontario', 'Quebec', 'Nova Scotia') ),
				array('provinces', 'type' => 'radio', 'options' => array('Ontario', 'Quebec', 'Nova Scotia') ),
				array('datestamp', 'type' => 'hidden', 'value' => date('Y m d') ),
				array('date', 'type' => 'datepicker'),
				array('first name', 'type' => 'text'),
				array('file', 'type'=> 'file')
		) );

///Set Email Settings
$email = array (
		'settings' => array(
				'addAddress' => 'diego@limeadvertising.com',
				'addReplyTo' => array('info@example.com', 'Information'),
				//	'addCC' => array ('cc@example.com' ),
				//	'addBCC' => array('bcc@example.com' ),
				'Subject' => 'Test Submission Like Whoa' ),
		'variables' => array(
				'name' => array ('first name', 'last name' ),
				'email' => 'email',
				'addAttachment' => 'file'
		) );

/* Here's Where the Magic Happens*/
$test = new lime_form();

$test->email	( 	$email  );
$test->settings	(   $attr['settings'] );
$test->fields	(	$attr['fields'] );
$test->db_connect( 	$db );
$test->spam		(	array('honeypot', 'date') );
$test->db		(	array('table' => 'form',
						  'fields' => array( 'data' => array( 'first name','last name','email','comment' ))
						 )
				);

include ( "test/header.php" );

include ( "test/body_form.php" );

include ( "test/footer.php" );