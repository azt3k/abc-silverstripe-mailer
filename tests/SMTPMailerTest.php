<?php
class SMTPMailerTest extends SapphireTest
{

    public function testSetup() {

        Object::useCustomClass('Email', 'SMTPEmail');
        Email::set_mailer(new SmtpMailer);
        SMTPEmail::set_mailer(new SmtpMailer);

        $mailer = Email::mailer();
        $this->assertEquals('SmtpMailer', get_class($mailer));

        $mailer = SMTPEmail::mailer();
        $this->assertEquals('SmtpMailer', get_class($mailer));
    }

    /**
     * @depends testSetup
     */
    public function testSMTPMailerSetConf() {

        // phpunit is a bit broken so we manually call the dependent tests;
        $this->testSetup();

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
            'debug'             => 0,
            'lang'              => 'en'
        );

        SMTPMailer::set_conf($conf_in);

        $conf_out = (array) SMTPMailer::get_conf();

        $this->assertEquals($conf_in, $conf_out);
    }

    /**
     * @depends testSMTPMailerSetConf
     */
    public function testEmail() {

        // phpunit is a bit broken so we manually call the dependent tests;
        $this->testSMTPMailerSetConf();

        $e = new Email();
        $e->To = "abc-silverstripe-mailer@mailinator.com";
        $e->Subject = "Hi there";
        $e->Body = "I just really wanted to email you and say hi.";

        $this->assertEquals(true, $e->send());

    }

    /**
     * @depends testSMTPMailerSetConf
     */
    public function testSMTPEmail() {

        // phpunit is a bit broken so we manually call the dependent tests;
        $this->testSMTPMailerSetConf();

        $e = new SMTPEmail();
        $e->To = "abc-silverstripe-mailer@mailinator.com";
        $e->Subject = "Hi there";
        $e->Body = "I just really wanted to email you and say hi.";

        $this->assertEquals(true, $e->send());

    }

    // disabling this test because Email Doesn't come out as SMTP email in these tests
    // /**
    //  * @depends testEmail
    //  */
    // public function testEmailCustomHeaders() {
    //
    //     // phpunit is a bit broken so we manually call the dependent tests;
    //     $this->testEmail();
    //
    //     $bcc = "abc-silverstripe-mailer-bcc@mailinator.com";
    //     $cc = "abc-silverstripe-mailer-cc@mailinator.com";
    //     $replyto = "abc-silverstripe-mailer-reply-to@mailinator.com";
    //
    //     $e = new Email();
    //     $e->To = "abc-silverstripe-mailer@mailinator.com";
    //     $e->Subject = "Hi there";
    //     $e->Body = "I just really wanted to email you and say hi.";
    //     $e->Cc = $cc;
    //     $e->Bcc = $bcc;
    //     $e->ReplyTo = $replyto;
    //
    //     // get the mailer bound to the Email class
    //     $e->setupMailer();
    //     $mailer = Email::mailer()->mailer;
    //
    //     // check bccs
    //     $bccs = $mailer->getBccAddresses();
    //     $this->assertEquals(true, in_array($bcc, $bccs));
    //
    //     // check ccs
    //     $ccs = $mailer->getCcAddresses();
    //     $this->assertEquals(true, in_array($cc, $ccs));
    //
    //     // check replytos
    //     $replytos = $mailer->getReplyToAddresses();
    //     $this->assertEquals(true, in_array($reployto, $reploytos));
    //
    //     // check send
    //     // $this->assertEquals(true, $e->send());
    //
    // }

    /**
     * @depends testSMTPEmail
     */
    public function testSMTPEmailCustomHeaders()
    {

        // phpunit is a bit broken so we manually call the dependent tests;
        $this->testSMTPEmail();

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
        $e->setupMailer();
        $mailer = SMTPEmail::mailer()->mailer;

        // check bccs
        $bccs = $mailer->getBccAddresses();
        $exists = false;
        foreach ($bccs as $item) {
            if (in_array($bcc, $item)) $exists = true;
        }
        $this->assertEquals(true, $exists);

        // check ccs
        $ccs = $mailer->getCcAddresses();
        $exists = false;
        foreach ($ccs as $item) {
            if (in_array($cc, $item)) $exists = true;
        }
        $this->assertEquals(true, $exists);

        // check replytos
        $replytos = $mailer->getReplyToAddresses();
        $exists = false;
        foreach ($replytos as $item) {
            if (in_array($replyto, $item)) $exists = true;
        }
        $this->assertEquals(true, $exists);

        // check send
        $this->assertEquals(true, $e->send());

    }

    /**
     * @depends testSMTPEmailCustomHeaders
     */
    public function testMultipleRecipients() {

        // phpunit is a bit broken so we manually call the dependent tests;
        $this->testSMTPEmailCustomHeaders();

        $to = 'abc-silverstripe-mailer@mailinator.com';
        $to2 = 'abc-silverstripe-mailer-2@mailinator.com';

        $e = new SMTPEmail();
        $e->To = $to . ', ' . $to2;
        $e->Subject = "Hi there";
        $e->Body = "I just really wanted to email you and say hi.";

        // get the mailer bound to the Email class
        $e->setupMailer();
        $mailer = SMTPEmail::mailer()->mailer;

        // check recipients
        $tos = $mailer->getToAddresses();
        $exists = $exists2 = false;
        foreach ($tos as $item) {
            if (in_array($to, $item)) $exists = true;
            if (in_array($to2, $item)) $exists2 = true;
        }
        $this->assertEquals(true, $exists && $exists2);

        // check send
        $this->assertEquals(true, $e->send());

    }

    /**
     * @depends testSMTPEmail
     */
    public function testFSAttachmentEmail() {

        // phpunit is a bit broken so we manually call the dependent tests;
        $this->testSMTPEmail();

        // create file
        $fileContents = 'test content';
        $fileName = 'test.txt';
        $type = 'text/plain';
        $absFileName = sys_get_temp_dir() . '/' . $fileName;
        file_put_contents($absFileName, $fileContents);

        // create email
        $e = new SMTPEmail();
        $e->To = 'abc-silverstripe-mailer@mailinator.com';
        $e->Subject = "Hi there";
        $e->Body = "I just really wanted to email you and say hi.";
        $e->attachFile($absFileName, $fileName, $type);

        // get the mailer bound to the Email class
        $e->setupMailer();
        $mailer = SMTPEmail::mailer()->mailer;

        // check attached files
        $files = $mailer->getAttachments();
        $this->assertEquals(true, $files[0][0] == $fileContents);
        $this->assertEquals(true, $files[0][1] == $fileName);
        $this->assertEquals(true, $files[0][4] == $type);

        // check send
        $this->assertEquals(true, $e->send());

    }

    /**
     * @depends testSMTPEmail
     */
    public function testStringAttachmentEmail() {

        // phpunit is a bit broken so we manually call the dependent tests;
        $this->testSMTPEmail();

        // create file
        $fileContents = 'test content';
        $fileName = 'test.txt';
        $type = 'text/plain';

        // create email
        $e = new SMTPEmail();
        $e->To = 'abc-silverstripe-mailer@mailinator.com';
        $e->Subject = "Hi there";
        $e->Body = "I just really wanted to email you and say hi.";
        $e->attachFileFromString($fileContents, $fileName, $type);

        // get the mailer bound to the Email class
        $e->setupMailer();
        $mailer = SMTPEmail::mailer()->mailer;

        // check attached files
        $files = $mailer->getAttachments();
        $this->assertEquals(true, $files[0][0] == $fileContents);
        $this->assertEquals(true, $files[0][1] == $fileName);
        $this->assertEquals(true, $files[0][4] == $type);

        // check send
        $this->assertEquals(true, $e->send());

    }
}
