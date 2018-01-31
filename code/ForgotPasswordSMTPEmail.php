<?php
/**
* Class used as template to send the forgot password email
*
* @package framework
* @subpackage security
*/
class ForgotPasswordSMTPEmail extends SMTPEmail {
    protected $from = '';  // setting a blank from address uses the site's default administrator email
    protected $subject = '';
    protected $ss_template = 'ForgotPasswordEmail';

    public function __construct() {

    $this->subject = _t('Member.SUBJECTPASSWORDRESET', "Your password reset link", 'Email subject');
    }
}