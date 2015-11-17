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

        $bcc = "abc-silverstripe-mailer-bcc@mailinator.com";
        $cc = "abc-silverstripe-mailer-cc@mailinator.com";
        $replyto = "abc-silverstripe-mailer-reply-to@mailinator.com";

        $e = new Email();
        $e->To = "abc-silverstripe-mailer@mailinator.com";
        $e->Subject = "Hi there";
        $e->Body = "I just really wanted to email you and say hi.";
        $e->Cc = $cc;
        $e->Bcc = $bcc;
        $e->ReplyTo = $replyto;

        // get the mailer bound to the Email class
        $mailer = Email::mailer();

        // check bccs
        $bccs = $mailer->getBccAddresses();
        $this->assertEquals(true, in_array($bcc, $bccs));

        // check ccs
        $ccs = $mailer->getCcAddresses();
        $this->assertEquals(true, in_array($cc, $ccs));

        // check replytos
        $replytos = $mailer->getReplyToAddresses();
        $this->assertEquals(true, in_array($reployto, $reploytos));

        // check send
        $this->assertEquals(true, $e->send());

    }
    /**
     * @depends testSMTPEmail
     */
    public function testSMTPEmailCustomHeaders()
    {

        $bcc = "abc-silverstripe-mailer-bcc@mailinator.com";
        $cc = "abc-silverstripe-mailer-cc@mailinator.com";
        $replyto = "abc-silverstripe-mailer-reply-to@mailinator.com";

        $e = new SMTPEmail();
        $e->To = "abc-silverstripe-mailer@mailinator.com";
        $e->Subject = "Hi there";
        $e->Body = "I just really wanted to email you and say hi.";
        $e->Cc = $cc;
        $e->Bcc = $bcc;
        $e->ReplyTo = $replyto;

        // get the mailer bound to the Email class
        $mailer = SMTPEmail::mailer();

        // check bccs
        $bccs = $mailer->getBccAddresses();
        $this->assertEquals(true, in_array($bcc, $bccs));

        // check ccs
        $ccs = $mailer->getCcAddresses();
        $this->assertEquals(true, in_array($cc, $ccs));

        // check replytos
        $replytos = $mailer->getReplyToAddresses();
        $this->assertEquals(true, in_array($reployto, $reploytos));

        // check send
        $this->assertEquals(true, $e->send());

    }
}
