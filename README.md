Setup
-----



add the following to you composer.json file:

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
"require": {
    "azt3k/abc-silverstripe-mailer" : "dev-master"
}
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~



Add something like the following your mysite/_config.php file:

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
SmtpMailer::set_conf(array(
    'default_from'      => array(
                               'name'  => 'admin',
                               'email' => 'admin@localhost'
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
));
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

or

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
SmtpMailer::set_conf(array(
    'default_from'      => array(
                               'name'  => 'user',
                               'email' => 'user@gmail.com'
                           ),
    'charset_encoding'  => 'utf-8',
    'server'            => 'smtp.gmail.com',
    'port'              => 587,
    'secure'            => 'tls',
    'authenticate'      => true,
    'user'              => 'user@gmail.com',
    'pass'              => 'password',
    'debug'             => 0,
    'lang'              => 'en'
));
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~