<?php

class SmtpMailer extends Mailer {

    public $mailer = null;

    private static $defaults = array(
        'default_from'      =>  array(
            'name'  => 'admin',
            'email' => 'admin@localhost'
        ),
        'charset_encoding'  => 'utf-8',
        'server'            => 'localhost',
        'port'              => 25,
        'secure'            => null,
        'authenticate'      => false,
        'user'              => '',
        'pass'              => '',
        'debug'             => 0,
        'lang'              => 'en'
    );

    /**
     * @config
     */
    private static $conf = array();

    /**
     * Constructor
     *
     * @param Mailer $mailer
     */
    public function __construct($mailer = null) {
        $this->mailer = $mailer;
    }

    /**
     *  @param  array|object $conf An associative array containing the configuration - see static::$conf for an example
     *  @return void
     */
    public static function set_conf($conf) {
        $conf = (array) $conf;
        static::$conf = static::array_merge_recursive_distinct(static::$conf, $conf);
    }

    /**
     *  @return stdClass
     */
    public static function get_conf() {
        return (object) static::array_merge_recursive_distinct(static::$defaults, static::$conf);
    }

    /**
     * @return void
     */
    protected static function set_conf_from_yaml() {
        $conf = (array) Config::inst()->get('SmtpMailer', 'conf');
        // die(print_r($conf,1));
        if (!empty($conf))
            static::$conf = static::array_merge_recursive_distinct(static::$conf, $conf);
    }

    /**
     *  @return void
     */
    public function configure() {

        // configure from YAML if available
        static::set_conf_from_yaml();

        // get the configuration
        $conf = static::get_conf();

        if ( !$this->mailer ) {
            $this->mailer = new PHPMailer( true );
            $this->mailer->IsSMTP();
            $this->mailer->CharSet = $conf->charset_encoding;
            $this->mailer->Host = $conf->server;
            $this->mailer->Port = $conf->port;
            $this->mailer->SMTPSecure = $conf->secure;
            $this->mailer->SMTPAuth = $conf->authenticate;
            if ( $this->mailer->SMTPAuth ) {
                $this->mailer->Username = $conf->user;
                $this->mailer->Password = $conf->pass;
            }
            $this->mailer->SMTPDebug = $conf->debug;
            $this->mailer->SetLanguage( $conf->lang );
        }

        // chain me
        return $this;
    }

    /**
     * Overwriting Mailer's function
     *
     *  @param string   $to             the recipient
     *  @param string   $from           the sender
     *  @param string   $subject        the subject
     *  @param string   $plainContent   plain text content
     *  @param array    $attachedFiles  an array of files to attach
     *  @param array    $customheaders  an array of custom headers to attach
     *  @return boolean
     */
    public function sendPlain($to, $from, $subject, $plainContent, $attachedFiles = array(), $customheaders = false) {
        $this->configure();
        $this->mailer->IsHTML( false );
        $this->mailer->Body = $plainContent;
        return $this->sendMailViaSmtp( $to, $from, $subject, $attachedFiles, $customheaders, false );
    }


    /**
     * Overwriting Mailer's function
     *
     *  @param string   $to             the recipient
     *  @param string   $from           the sender
     *  @param string   $subject        the subject
     *  @param string   $htmlContent    html content
     *  @param array    $attachedFiles  an array of files to attach
     *  @param array    $customheaders  an array of custom headers to attach
     *  @param string   $plainContent   plaintext alternative
     *  @param array    $inlineImages   an array of image files to attach inline
     *  @return boolean
     */
    public function sendHTML($to, $from, $subject, $htmlContent, $attachedFiles = array(), $customheaders = false, $plainContent = false, $inlineImages = false) {
        $this->configure();
        $this->mailer->IsHTML( true );
        if( $inlineImages ) {
            $this->mailer->MsgHTML( $htmlContent, Director::baseFolder() );
        } else {
            $this->mailer->Body = $htmlContent;
            if (empty($plainContent)) $plainContent = trim( Convert::html2raw( $htmlContent ) );
            $this->mailer->AltBody = $plainContent;
        }
        return $this->sendMailViaSmtp( $to, $from, $subject, $attachedFiles, $customheaders, $inlineImages );
    }

    /**
     *  @param string   $to             the recipient
     *  @param string   $from           the sender
     *  @param string   $subject        the subject
     *  @param array    $attachedFiles  an array of files to attach
     *  @param array    $customheaders  an array of custom headers to attach
     *  @param array    $inlineImages   an array of image files to attach inline
     *  @return boolean
     */
    public function setupMailer($to, $from, $subject, $attachedFiles = array(), $customheaders = false) {

        // init this for later if we need it
        $msgForLog = '';

        // make sure we have a mailer
        if (!$this->mailer) $this->configure();

        // default result
        $result = false;

        // default from
        if (!$from) $from =  static::$conf['default_from']['email'];

        // try to update stuff
        try {

            $this->buildBasicMail( $to, $from, $subject );
            $this->addCustomHeaders( $customheaders );
            $this->attachFiles( (array) $attachedFiles );
            $result = true;

        } catch( phpmailerException $e ) {
            $this->handleError( $e->errorMessage(), $msgForLog );
        } catch( Exception $e ) {
            $this->handleError( $e->getMessage(), $msgForLog );
        }

        return $result;
    }

    /**
     *  @param string   $to             the recipient
     *  @param string   $from           the sender
     *  @param string   $subject        the subject
     *  @param array    $attachedFiles  an array of files to attach
     *  @param array    $customheaders  an array of custom headers to attach
     *  @param array    $inlineImages   an array of image files to attach inline
     *  @return boolean
     */
    protected function sendMailViaSmtp( $to, $from, $subject, $attachedFiles = array(), $customheaders = false, $inlineImages = false ) {

        // default result
        $result = false;

        if( $this->mailer->SMTPDebug > 0 ) echo "<em><strong>*** Debug mode is on</strong>, printing debug messages and not redirecting to the website:</em><br />";
        $msgForLog = "\n*** The sender was : $from\n*** The message was :\n{$this->mailer->AltBody}\n";

        try {

            // try to send
            if ($this->setupMailer($to, $from, $subject, $attachedFiles, $customheaders))
                $result = $this->mailer->send();

            if( $this->mailer->SMTPDebug > 0 ) {
                echo "<em><strong>*** E-mail to $to has been sent.</strong></em><br />";
                echo "<em><strong>*** The debug mode blocked the process</strong> to avoid the url redirection. So the CC e-mail is not sent.</em>";
            }

        } catch( phpmailerException $e ) {
            $this->handleError( $e->errorMessage(), $msgForLog );
        } catch( Exception $e ) {
            $this->handleError( $e->getMessage(), $msgForLog );
        }

        return $result;
    }

    /**
     *  @param string $e            The error message - usually Exception::errorMessage()
     *  @param string $msgForLog    The message for the SS log
     *  @param return void
     */
    public function handleError( $e, $msgForLog )
    {
        $msg = $e . $msgForLog;
        SS_Log::log($msg, Zend_Log::ERR);
        throw new Exception($msg);
    }

    /**
     *  @param  string $to
     *  @param  string $from
     *  @param  string $subject
     *  @return void
     */
    protected function buildBasicMail( $to, $from, $subject )
    {
        if( preg_match('/(\'|")(.*?)\1[ ]+<[ ]*(.*?)[ ]*>/', $from, $from_split ) ) {
            // If $from countain a name, e.g. "My Name" <me@acme.com>
            $this->mailer->SetFrom( $from_split[3], $from_split[2] );
        }
        else {
            $this->mailer->SetFrom( $from );
        }

        // set subject
        $this->mailer->Subject = $subject;

        // clear addresses
        $this->mailer->ClearAddresses();

        // split addresses
        $tos = explode(',', $to);

        // add addresses
        foreach ($tos as $addr) {

            // clean
            $addr = trim($addr);

            // validate
            // if (Email::validEmailAddress($addr)) {

                // For the recipient's name, the string before the @ from the e-mail address is used
                // this doesn't support the "Mr Nobody <mr.nobobdygmail.com>" syntax
                $this->mailer->AddAddress($addr, ucfirst(substr($addr, 0, strpos( $addr, '@' ))));
            // }
        }

    }

    /**
     *  @param  array $headers
     *  @return void
     */
    protected function addCustomHeaders( $headers ) {

        if( !$headers or !is_array($headers) ) $headers = array();
        if( !isset( $headers["X-Mailer"] ) ) $headers["X-Mailer"] = X_MAILER;
        if( !isset( $headers["X-Priority"] ) ) $headers["X-Priority"] = 3;

        // clear existing headers
        $this->mailer->ClearCustomHeaders();

        // look at all the headers and handle appropriately
        foreach ($headers as $header_name => $header_value) {

            // split
            if (in_array(strtolower($header_name), array('cc', 'bcc', 'reply-to', 'replyto')))
                $addresses = preg_split('/(,|;)/', $header_value);

            // call setters rather than setting headers for:
            //  - bcc
            //  - cc
            //  - reply-to
            switch (strtolower($header_name)) {
                case 'cc':
                    foreach ($addresses as $address) {
                        $this->mailer->addCC($address);
                    }
                    break;
                case 'bcc':
                    foreach ($addresses as $address) {
                        $this->mailer->addBCC($address);
                    }
                    break;
                case 'reply-to':
                case 'replyto':
                    foreach ($addresses as $address) {
                        $this->mailer->addReplyTo($address);
                    }
                    break;
                default:
                    $this->mailer->AddCustomHeader($header_name . ':' . $header_value);
                    break;
            }
        }
    }

    /**
     *  @param  array $attachedFiles
     *  @return void
     */
    protected function attachFiles( array $attachedFiles ) {
        if( !empty( $attachedFiles ) && is_array( $attachedFiles ) ) {
            foreach( $attachedFiles as $attachedFile ) {

                // all attached files are stashed as strings in the attached files array
                // see Email and SMTPEMail classes for more info
                $this->mailer->AddStringAttachment(
                    $attachedFile['contents'],
                    $attachedFile['filename'],
                    'base64',
                    $attachedFile['mimetype']
                );

            }
        }
    }

    /**
     *  @param  array $array1 The first array
     *  @param  array $array2 The second array
     *  @return array the merged array
     */
    protected static function array_merge_recursive_distinct( array $array1, array $array2 ) {

        $merged = $array1;

        foreach ( $array2 as $key => $value ) {
            if ( is_array( $value ) && isset( $merged [$key] ) && is_array( $merged [$key] ) ) {
                $merged [$key] = static::array_merge_recursive_distinct( $merged [$key], $value );
            } else {
                $merged [$key] = $value;
            }
        }

        return $merged;
    }

}
