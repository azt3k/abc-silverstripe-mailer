<?php
class SMTPMailerTest extends SapphireTest
{

    public function testSMTPMailerSetConf() {

        $conf_in = array(
            'default_from'      =>  array(
                'name'  => 'abc-silverstripe-mailer-from',
                'email' => 'abc-silverstripe-mailer-from@mailinator.com'
            ),
            'charset_encoding'  => 'utf-8',
            'server'            => 'mail.mailinator.com',
            'port'              => 25,
            'secure'            => null,
            'authenticate'      => false,
            'user'              => '',
            'pass'              => '',
            'debug'             => 2,
            'lang'              => 'en'
        );

        SMTPMailer::set_conf($conf_in);

        $conf_out = (array) SMTPMailer::get_conf();

        $this->assertEquals($conf_in, $conf_out);
    }

    /**
     * @depends testSMTPMailerSetConf
     */
    public function testEmail()
    {

        $e = new Email();
        $e->To = "abc-silverstripe-mailer@mailinator.com";
        $e->Subject = "Hi there";
        $e->Body = "I just really wanted to email you and say hi.";

        $this->assertEquals(true, $e->send());

    }

    /**
     * @depends testSMTPMailerSetConf
     */
    public function testSMTPEmail()
    {

        $e = new SMTPEmail();
        $e->To = "abc-silverstripe-mailer@mailinator.com";
        $e->Subject = "Hi there";
        $e->Body = "I just really wanted to email you and say hi.";

        $this->assertEquals(true, $e->send());

    }

    /**
     * @depends testEmail
     */
    public function testEmailCustomHeaders()
    {

        $e = new Email();
        $e->To = "abc-silverstripe-mailer@mailinator.com";
        $e->Subject = "Hi there";
        $e->Body = "I just really wanted to email you and say hi.";
        $e->Cc = "abc-silverstripe-mailer-cc@mailinator.com";
        $e->Bcc = "abc-silverstripe-mailer-bcc@mailinator.com";
        $e->ReplyTo = "abc-silverstripe-mailer-reply-to@mailinator.com";

        $this->assertEquals(true, $e->send());

    }
    /**
     * @depends testSMTPEmail
     */
    public function testSMTPEmailCustomHeaders()
    {

        $e = new SMTPEmail();
        $e->To = "abc-silverstripe-mailer@mailinator.com";
        $e->Subject = "Hi there";
        $e->Body = "I just really wanted to email you and say hi.";
        $e->Cc = "abc-silverstripe-mailer-cc@mailinator.com";
        $e->Bcc = "abc-silverstripe-mailer-bcc@mailinator.com";
        $e->ReplyTo = "abc-silverstripe-mailer-reply-to@mailinator.com";

        $this->assertEquals(true, $e->send());

    }
}
