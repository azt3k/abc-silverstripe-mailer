<?php

class SMTPEmail extends Email {

    /**
     * makes ReplyTo work like BCC etc
     * @return [type] [description]
     */
    public function getReplyTo() {
        $h = $this->customHeaders;
        return !empty($h['Reply-To']) ? $h['Reply-To'] : null;
    }

    /**
     * makes ReplyTo work like BCC etc
     * @param [type] $val [description]
     */
    public function setReplyTo($val) {
        $this->replyTo($val);
        return $this;
    }

    /**
     * proxy method for file attachments
     * @param  [type] $filename         [description]
     * @param  [type] $attachedFilename [description]
     * @param  [type] $mimetype         [description]
     * @return [type]                   [description]
     */
    public function attachFile($filename, $attachedFilename = null, $mimetype = null) {

        // generate a name for the file if we aren't supplied one
        if (!$attachedFilename) $attachedFilename = trim(str_replace(Director::baseFolder(), '', $filename), '/');

        // Get the full path
        if (!file_exists($filename)) $filename = Director::getAbsFile($filename);

        // try to attach the file
        if (file_exists($filename)) {
            $this->attachFileFromString(file_get_contents($filename), $attachedFilename, $mimetype);
        }

        // throw
        else {
            throw new Exception("Could not attach '$filename' to email. File does not exist.");
        }

        return $this;
    }

    /**
     * this is mainly a test harness
     * @return [type] [description]
     */
    public function setupMailer() {

        Requirements::clear();

        $this->parseVariables(true);

        if(empty($this->from)) $this->from = Email::config()->admin_email;

        $headers = $this->customHeaders;

        if(project()) $headers['X-SilverStripeSite'] = project();

        $to = $this->to;
        $from = $this->from;
        $subject = $this->subject;
        if ($sendAllTo = $this->config()->send_all_emails_to) {
            $subject .= " [addressed to $to";
            $to = $sendAllTo;
            if($this->cc) $subject .= ", cc to $this->cc";
            if($this->bcc) $subject .= ", bcc to $this->bcc";
            $subject .= ']';
            unset($headers['Cc']);
            unset($headers['Bcc']);
        } else {
            if($this->cc) $headers['Cc'] = $this->cc;
            if($this->bcc) $headers['Bcc'] = $this->bcc;
        }

        if ($ccAllTo = $this->config()->cc_all_emails_to) {
            if(!empty($headers['Cc']) && trim($headers['Cc'])) {
                $headers['Cc'] .= ', ' . $ccAllTo;
            } else {
                $headers['Cc'] = $ccAllTo;
            }
        }

        if ($bccAllTo = $this->config()->bcc_all_emails_to) {
            if(!empty($headers['Bcc']) && trim($headers['Bcc'])) {
                $headers['Bcc'] .= ', ' . $bccAllTo;
            } else {
                $headers['Bcc'] = $bccAllTo;
            }
        }

        if ($sendAllfrom = $this->config()->send_all_emails_from) {
            if($from) $subject .= " [from $from]";
            $from = $sendAllfrom;
        }

        Requirements::restore();

        return self::mailer()->setupMailer($to, $from, $subject, $this->attachments, $headers);

    }

}
