[![Build Status](https://travis-ci.org/azt3k/abc-silverstripe-mailer.svg?branch=master)](https://travis-ci.org/azt3k/abc-silverstripe-mailer)

Setup
-----

Add the following to you composer.json file:

```json
"require": {
    "azt3k/abc-silverstripe-mailer" : "*@stable"
}
```

Add something like the following your `mysite/_config.php` file:

````php
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
````
or

````php
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
````

Alternatively insert the configuration into your `project/_config/config.yml` file

````yaml
SmtpMailer:
  conf:
    default_from:
      name: user
      email: user@gmail.com
    charset_encoding: utf-8
    server: smtp.gmail.com
    port: 587
    secure: tls
    authenticate: true
    user: user@gmail.com
    pass: password'
    debug: 0
    lang: en
````


License
-------


Copyright (c) 2015, azt3k
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

3. Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
