<?php

// deprecated with any luck
// Object::useCustomClass('Email', 'SMTPEmail');

Email::set_mailer(new SmtpMailer);
SMTPEmail::set_mailer(new SmtpMailer);
