<?php

if (class_exists('Email')) Email::set_mailer(new SmtpMailer);
SMTPEmail::set_mailer(new SmtpMailer);
