<?
require_once "lib/messages/uiMessages.php";

class DataException extends Exception {
	var $shortcode;
	var $params;

	function DataException($shortcode,$params=null) {
		$this->shortcode = $shortcode;
		$this->params = $params;
	}

	function getUIMessage() {
		$msg=uiMessages::getMessage($this->shortcode);
		if ($this->params) {
			$msg = vsprintf($msg, $this->params);
		}
		return $msg;
	}
}

?>
