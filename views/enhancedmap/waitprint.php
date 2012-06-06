<?php defined('SYSPATH') or die('No direct script access.');
/**
 * View for telling the user to wait while the server makes a bitmap map of the open layers map 
 * 
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
<title>Wait to Print</title> 
<link rel="stylesheet" type="text/css" href="<?php echo url::site();?>media/css/error.css" /> 
</head> 
 
<body> 
<div  style="margin:30px;postion:relative;top:auto;left:auto;width:auto;height:auto;text-align:center;background-image:none;" id="error"> 
	<h1 style="height:auto;margin:0px;padding:20px;text-align:center;">Please wait while we render your image</h1> 
	<img src="<?php echo url::site(); ?>media/img/loading_g2.gif"/>   
</div> 
</body> 
</html>