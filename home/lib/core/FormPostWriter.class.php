<?php

class FormPostWriter {
	function FormPostXMLWriter() {
	}

	function addError($fieldName, $errorMsg) {
		$alertimg='<img style="vertical-align:bottom" src="images/error.png" />&nbsp;';
		if (strpos($errorMsg, '<br>') === false) {
			$errorMsg = $alertimg.$errorMsg;
		} else {
			$errorMsg = '<br>'.$alertimg.substr($errorMsg,4);
		}
		print "error.$fieldName=$errorMsg\n";
	}

	function addStatus($formId, $formStatus, $errorCode) {
		print "formId=$formId\n";
		print "status=$formStatus\n";
		print "errorCode=$errorCode\n";
	}
}

?>
