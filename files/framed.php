<?PHP
//This file surrounds the generated html with the rest of a webpage so it can be framed.
header('Access-Control-Allow-Origin: *'); //Let the file be iframed
header('Content-Security-Policy: *'); 
if(preg_match("/^[1-9][0-9]*$/", $_GET['id'])) // only numbers allowed
$readinglist= $_GET['id'];
else
exit("Not a Number");
?>
<!doctype html>
<html lang="en">
  <head>
<?PHP // include_once '../../includes/meta.php'; // your website css files ?>

<?PHP
$pagetitle="Library Directory";
?>
</head>
<body>

<?PHP
$readinglistfile = $readinglist.'.html'; //embed the static HTML file also used in javascript embeds
if(file_exists(stream_resolve_include_path($readinglistfile))){
	clearstatcache();
	if(filesize($readinglistfile) > 200){
		include($readinglistfile);
	}
}	
?>

</body>
</html>