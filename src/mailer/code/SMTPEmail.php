<?php

class SMTPEmail extends Email {
	
	public function attachFile($filename, $attachedFilename = null, $mimetype = null) {
		if(!$attachedFilename) $attachedFilename = str_replace(Director::baseFolder(),'',$filename);
		$absoluteFileName = Director::getAbsFile($filename);
		if(file_exists($absoluteFileName)) {
			$this->attachFileFromString(file_get_contents($absoluteFileName), $attachedFilename, $mimetype);
		} else {
			user_error("Could not attach '$absoluteFileName' to email. File does not exist.", E_USER_NOTICE);
		}
		return $this;
	}

}