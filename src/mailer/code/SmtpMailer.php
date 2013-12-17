<?php
 
class SmtpMailer extends Mailer {

    public $mailer = null;

    protected static $conf = array(
        'default_from'      =>  array(
            'name'  => 'admin',
            'email' =>'admin@localhost'                     
        ),
        'charset_encoding'  => 'utf-8',
        'server'            => 'localhost',
        'port'              => 25,
        'secure'            => null,
        'authenticate'      => false,
        'user'              => 'username',
        'pass'              => 'password',
        'debug'             => 0,
        'lang'              => 'en'
    );

    /**
     * Constructor
     * 
     * @param Mailer $mailer
     */
    public function __construct($mailer = null) {
        $this->mailer = $mailer;
    }    

    /**
     *  @param  array|object $conf An associative array containing the configuration - see self::$conf for an example
     *  @return void
     */
    public static function set_conf($conf) {
        $conf = (array) $conf;
        $class = get_called_class();
        $class::$conf = $class::array_merge_recursive_distinct($class::$conf, $conf);
    }

    /**
     *  @return stdClass
     */
    public static function get_conf() {
        $class = get_called_class();
        return (object) $class::$conf;
    }    

    /**
     *  @return void
     */
    protected function configure() {
        $conf = self::get_conf();
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
	public function sendPlain($to, $from, $subject, $plainContent, $attachedFiles = false, $customheaders = false) {
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
	public function sendHTML($to, $from, $subject, $htmlContent, $attachedFiles = false, $customheaders = false, $plainContent = false, $inlineImages = false) {
	    $this->configure();
        $this->mailer->IsHTML( true );
        if( $inlineImages ) {
            $this->mailer->MsgHTML( $htmlContent, Director::baseFolder() );
        } else {
            $this->mailer->Body = $htmlContent;
            if( empty( $plainContent ) ) $plainContent = trim( Convert::html2raw( $htmlContent ) );
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
    protected function sendMailViaSmtp( $to, $from, $subject, $attachedFiles = false, $customheaders = false, $inlineImages = false ) {

        // die(print_r($attachedFiles,1));
		
		$result = false;

        if (!$from) {
            $class = get_called_class();
            $from =  $class::$conf['default_from']['email'];
        }        
		
        if( $this->mailer->SMTPDebug > 0 ) echo "<em><strong>*** Debug mode is on</strong>, printing debug messages and not redirecting to the website:</em><br />";
        $msgForLog = "\n*** The sender was : $from\n*** The message was :\n{$this->mailer->AltBody}\n";

        try {
            $this->buildBasicMail( $to, $from, $subject );
            $this->addCustomHeaders( $customheaders );
            $this->attachFiles( $attachedFiles );
            $result = $this->mailer->Send();

            if( $this->mailer->SMTPDebug > 0 ) {
                echo "<em><strong>*** E-mail to $to has been sent.</strong></em><br />";
                echo "<em><strong>*** The debug mode blocked the process</strong> to avoid the url redirection. So the CC e-mail is not sent.</em>";
                die();
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
        Debug::log( $msg );
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
        if( preg_match('/(\'|")(.*?)\1[ ]+<[ ]*(.*?)[ ]*>/', $from, $from_splitted ) ) {
            // If $from countain a name, e.g. "My Name" <me@acme.com>
            $this->mailer->SetFrom( $from_splitted[3], $from_splitted[2] );
        }
        else {
            $this->mailer->SetFrom( $from );
        }

        // not entirely sure what this will do
        if (!Email::validEmailAddress($to)) $to = false;

        $this->mailer->ClearAddresses();
        $this->mailer->AddAddress( $to, ucfirst( substr( $to, 0, strpos( $to, '@' ) ) ) ); // For the recipient's name, the string before the @ from the e-mail address is used
        $this->mailer->Subject = $subject;
    }
    
    /**
     *  @param  array $headers
     *  @return void
     */    
    protected function addCustomHeaders( $headers ) {

    	if( !$headers or !is_array($headers) ) $headers = array();
	    if( !isset( $headers["X-Mailer"] ) ) $headers["X-Mailer"] = X_MAILER;
	    if( !isset( $headers["X-Priority"] ) ) $headers["X-Priority"] = 3;
	
	    $this->mailer->ClearCustomHeaders();
	    foreach( $headers as $header_name => $header_value ) {
	        $this->mailer->AddCustomHeader( $header_name.':'.$header_value );    
	    }

    }
    
    /**
     *  @param  array $attachedFiles 
     *  @return void
     */
    protected function attachFiles( array $attachedFiles ) {
        if( !empty( $attachedFiles ) and is_array( $attachedFiles ) ) {
            foreach( $attachedFiles as $attachedFile ) {
                $this->mailer->AddAttachment( Director::baseFolder().DIRECTORY_SEPARATOR.$attachedFile['filename'] );
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
                $merged [$key] = self::array_merge_recursive_distinct( $merged [$key], $value );
            } else {
                $merged [$key] = $value;
            }
        }

        return $merged;
    }    

}