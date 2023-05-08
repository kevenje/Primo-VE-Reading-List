<?PHP
$id = '101'; // The reading list ID sent from list.php or contained in a database record
$totalresults = 'Yes'; // Yes there were results for this list from list.php
$directorypath='/home/www/public_html/readinglist/';
$webpath ='https://library.sdsu.edu/readinglist/'; // for when the list is embedded externally on LibGuides, etc
?>
<!doctype html>
<html lang="en">
  <head>
<?PHP //include_once '../includes/meta.php'; // your website css ?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

	<script>
		//copy/paste script for code
		window.onload = function () {
		  // Get all the elements that match the selector as arrays
		  var copyTextareaBtn = Array.prototype.slice.call(document.querySelectorAll('.js-textareacopybtn'));
		  var copyTextarea = Array.prototype.slice.call(document.querySelectorAll('.js-copytextarea'));

		  // Loop through the button array and set up event handlers for each element
		  copyTextareaBtn.forEach(function(btn, idx){

			btn.addEventListener("click", function(){

			  // Get the textarea who's index matches the index of the button
			  copyTextarea[idx].select();

			  try {
				var msg = document.execCommand('copy') ? 'successful' : 'unsuccessful';
				console.log('Copying text command was ' + msg);
			  } catch (err) {
				console.log('Whoops, unable to copy');
			  } 

			});

		  });
		}	
	</script>
<?PHP
$pagetitle="Your Reading List";
?>
</head>
<body>
<?PHP // include_once '../includes/header.php'; //your website header ?>
<div class="container">
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="https://library.sdsu.edu">Library</a></li>
	<li class="breadcrumb-item"><a href="/readinglist">Reading Lists</a></li>
    <li class="breadcrumb-item active" aria-current="page">Your Reading List</li>
  </ol>
</nav>
	<?PHP if($totalresults =='Yes'){ ?>
	<div class="row">
		<div class="col">
				<script>
			$(document).ready(function(){
			$("#<?PHP echo $id; ?>").load("files/<?PHP echo $id; ?>.html");
			});
			</script>
			<div id="<?PHP echo $id; ?>"></div>
		</div>
		<div class="col">
		<div class="card">
  			<div class="card-body">
			<h3 class="card-title">Canvas</h3> 
				<h4 class="card-subtitle mb-2 text-muted">Get the iframed content</h4>
			<textarea rows="2" style="width:100%;" class="js-copytextarea">&lt;iframe src=&quot;<?PHP echo $webpath; ?>files/framed.php?id=<?PHP echo $id; ?>&quot; width=&quot;100%&quot; height=&quot;500px&quot;&gt;A Reading List&lt;/iframe&gt;
				</textarea>
				<button class="js-textareacopybtn">Copy To Clipboard</button>
				<a class="card-link" href="https://community.canvaslms.com/t5/Instructor-Guide/How-do-I-embed-media-from-an-external-source-in-the-Rich-Content/ta-p/828" target="_new">How To Embed</a><br/><br/>
			<h4 class="card-subtitle mb-2 text-muted">Get the static HTML code</h4>
		<textarea style="width:100%;height:300px;" class="js-copytextarea">
		<?PHP 
		$listhtml = file_get_contents("{$directorypath}files/{$id}.html"); 
		echo $listhtml;
		?>
		</textarea><button class="js-textareacopybtn">Copy To Clipboard</button> <a href="https://community.canvaslms.com/t5/Instructor-Guide/How-do-I-use-the-HTML-view-in-the-Rich-Content-Editor-as-an/ta-p/876" target="_new">How to Embed</a>
			</div>
		</div>
		<div class="card">
  			<div class="card-body">
			<h3 class="card-title">LibGuides (Librarians)</h3> 
		<h4 class="card-subtitle mb-2 text-muted">Copy the JavaScript code</h4>
		<textarea style="width:100%;height:100px;" class="js-copytextarea">&lt;script&gt; $(document).ready(function(){ $(&quot;#<?PHP echo $id; ?>&quot;).load(&quot;<?PHP echo $webpath; ?>files/<?PHP echo $id; ?>.html&quot;); }); &lt;/script&gt;
		&lt;div id=&quot;<?PHP echo $id; ?>&quot;&gt;&lt;/div&gt;
		</textarea><button class="js-textareacopybtn">Copy To Clipboard</button> <a href="https://ask.springshare.com/libguides/faq/939" target="_new">How To Embed</a><br/><br/>
		<h4 class="card-subtitle mb-2 text-muted">Copy the RSS feed</h4>
			<textarea rows="1" style="width:100%;" class="js-copytextarea"><?PHP echo $webpath; ?>files/<?PHP echo $id; ?>.rss</textarea><button class="js-textareacopybtn">Copy To Clipboard</button> <a href="https://ask.springshare.com/libguides/faq/963" target="_new">How to Embed</a>
					</div>
			</div>
		</div>
	</div>
	<?PHP } else { ?>
	<h2>This tag doesn't exist</h2>
	<p>Check your Primo Favorites tag and <a href="">try again</a>. </p>
	<?PHP } ?>
</div>
<?PHP // include_once '../includes/footer.php'; // your website footer ?>
</body>
</html>