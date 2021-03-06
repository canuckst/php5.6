<?php

class Messages extends CI_Model {

  function __construct()
  {
    // Call the Model constructor
    parent::__construct();
    $this->load->library('email');
  }

  function send($to, $subject, $message)
  {
    $config['protocol'] = 'sendmail';
    $config['mailpath'] = '/usr/sbin/sendmail';
    $config['charset'] = 'utf-8';
    $config['mailtype'] = 'html';
    $config['wordwrap'] = TRUE;

    $this->email->initialize($config);

    $this->email->from('onlineservice@canuckstuff.com', 'Canuck Uniforms');
    $this->email->to($to);
     
    $this->email->subject($subject);
    $this->email->message($message);
     
    if (!$this->email->send())
    {
    	log_message('error', 'Failed on send email to '.$to);
    }
    //echo $this->email->print_debugger();

     
  }


}
