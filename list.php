<?PHP
$key = ''; //from EL developers network, stored in database or hidden directory
// Key requires Primo Favorites (read access) and Primo Search (read access)
//This information normally stored in database after collecting with web form
$id = '101'; //determine a reading list id by using unique id from database row or generate one now - used for list file name
$listTitle ='All About Aliens'; //retrieved from Database or From input
$listTag = 'Alien Abduction'; //Primo Label retrieved from Database or form input, used to limit results to one tag

//to construct primo URL
$cdomain = 'csu-sdsu';
$cscope = 'All'; //best to include scope with Alma & CDI results as all may have been favorited
$ctab = 'All';
$cvid = '01CALS_SDL:01CALS_SDL';

$directorypath='/home/library/public_html/readinglist/';
$weblocation = 'https://library.sdsu.edu/readinglist/';

//function to determine if book cover is a single pixel
function retrieve_remote_file_size($url){
     $ch = curl_init($url);

     curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
     curl_setopt($ch, CURLOPT_HEADER, TRUE);
     curl_setopt($ch, CURLOPT_NOBODY, TRUE);

     $data = curl_exec($ch);
     $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

     curl_close($ch);
     return $size;
}

$objectrow ='';
//Get JWT User Token
$tokenurl = $weblocation.'alma_user_jwt.php'; // for production app you will need to retrieve user credentials Alma ID/User Group from database or input form
//$tokenurl = 'alma_user_jwt.php?userid=[almaid]&usergroup=[almagroup]' OR alma_user_jwt.php?listid=[db unique ID]' //if sending patron info from this page to user jwt
$token = file_get_contents($tokenurl);

//Favorites API
$url = 'https://api-na.hosted.exlibrisgroup.com/primo/v1/favorites?apikey='.$key; 
$options = array('http' => array(
    'method'  => 'GET',
    'header' => 'Authorization: Bearer '.$token
));
$context  = stream_context_create($options);
$response = file_get_contents($url, false, $context);
$data = json_decode($response, true);


$records = $data['records'];
$i=0;
foreach($records as $record){ //alma or primo

unset($iconurl);
unset($imgurl1);
unset($imgurl2);
unset($ispartof);
unset($publisher);
unset($publisher2);
$title  = $record['title'];
$author  = $record['author'];
$labels = $record['labels'];
if(in_array($listTag, $labels)) {
$i++;
$rawrecordid = $record['recordId'];

if(preg_match("/{alma}/i", $rawrecordid)){ //find alma records
	$context='L'; // context and adaptor needed to differentiate between alma and cdi in the search URL
	$adaptor='Local%20Search%20Engine';
}
else { //the rest are CDI records
	$context='PC';
	$adaptor='Primo%20Central';	
}
	
$recordid = str_replace('alma', '', $rawrecordid); //Remove "alma" from record id so it can be used to in the Search API

//Search API
$searchurl = 'https://api-na.hosted.exlibrisgroup.com/primo/v1/search?vid='.$cvid.'&scope='.$cscope.'&tab='.$ctab.'&offset=0&limit=1&q=any,contains,'.$recordid.'&sort=rank&lang=eng&pcAvailability=false&apikey='.$key;

$searchjson = file_get_contents($searchurl);
$searchdata = json_decode($searchjson,true);

if(!isset($title))
$title = $searchdata['docs'][0]['pnx']['display']['title'][0]; // Get the title from JSON if we don't have it from Favorites API
$type = $searchdata['docs'][0]['pnx']['display']['type'][0];
if(!isset($type)) //If there's no type make it an article as that cover image is pretty generic
$type = 'article';
$description = $searchdata['docs'][0]['pnx']['display']['description'][0];	

if($type == 'book'){ //Get this info if its a book
$publisher = $searchdata['docs'][0]['pnx']['display']['publisher'][0];	
$pubdate = $searchdata['docs'][0]['pnx']['addata']['date'][0];
$author = $searchdata['docs'][0]['pnx']['display']['creator'][0];
$isbns = $searchdata['docs'][0]['pnx']['addata']['isbn'];
if($isbns !=''){
if(is_array($isbns)) {
	foreach($isbns as $isbn => $isbnvalue){
		$coverurl = 'https://proxy-na.hosted.exlibrisgroup.com/exl_rewrite/syndetics.com/index.php?client=primo&isbn='.$isbnvalue.'/sc.jpg';
		$size = retrieve_remote_file_size($coverurl); //loop through ISBNs until you find a book cover (looking at image size)
		if($size > 100) {
		$iconurl = 'https://proxy-na.hosted.exlibrisgroup.com/exl_rewrite/syndetics.com/index.php?client=primo&isbn='.$isbnvalue.'/sc.jpg';
		break; //stop looking when you find a book cover by looking at image size
		}
	}
}
else //just use the generic book image
$iconurl = 'https://csu-sdsu.primo.exlibrisgroup.com/discovery/custom/01CALS_NETWORK-CENTRAL_PACKAGE/img/icon_book.png';
}
else //just use the generic book image
$iconurl = 'https://csu-sdsu.primo.exlibrisgroup.com/discovery/custom/01CALS_NETWORK-CENTRAL_PACKAGE/img/icon_book.png';
	
if($author =='') //if we don't have the author from favorites API get it from the Search API
$author = $searchdata['docs'][0]['pnx']['display']['contributor'][0]; 
if(preg_match('$$',$author)){
$authorexplode = explode("$$", $author); //remove characters from author 
$author = $authorexplode[0];
}
}
elseif($type == 'article'){
$iconurl = 'https://csu-sdsu.primo.exlibrisgroup.com/discovery/custom/01CALS_NETWORK-CENTRAL_PACKAGE/img/icon_article.png';
if($author =='') //if we don't have the author from favorites API get it from the Search API
$author = $searchdata['docs'][0]['pnx']['addata']['au'][0];
$ispartof = $searchdata['docs'][0]['pnx']['display']['ispartof'][0];	
}
elseif($type == 'journal'){
$iconurl = 'https://csu-sdsu.primo.exlibrisgroup.com/discovery/img/icon_journal.png';
$publisher = $searchdata['docs'][0]['pnx']['display']['publisher'][0];	
$publisher2 = $searchdata['docs'][0]['pnx']['display']['publisher'][1];	
$pubdate = '';

}
else { //if the book cover doesn't exist use the generic article image
$imgurl1 = 'https://csu-sdsu.primo.exlibrisgroup.com/discovery/custom/01CALS_NETWORK-CENTRAL_PACKAGE/img/icon_'.$type.'.png';
$imgurl2 = 'https://csu-sdsu.primo.exlibrisgroup.com/discovery/custom/01CALS_NETWORK-CENTRAL_PACKAGE/img/icon_article.png';
$imgurl1_size = retrieve_remote_file_size($imgurl1);
if($imgurl1_size > 100)
$iconurl = $imgurl1;
else
$iconurl = $imgurl2;

$publisher = $searchdata['docs'][0]['pnx']['display']['publisher'][0];	
$pubdate = $searchdata['docs'][0]['pnx']['addata']['date'][0];	

}

//Create HTML output
$objectrow .= "\t".'<div class="favoritesrow" style="width: 250px; padding:10px;">'."\r\n\t\t".'<a class="favoritesurl" href="https://'.$cdomain.'.primo.exlibrisgroup.com/discovery/fulldisplay?docid='.$rawrecordid.'&context='.$context.'&vid='.$cvid.'&lang=en&search_scope='.$cscope.'&adaptor='.$adaptor.'&tab='.$ctab.'" target="'.$recordid.'">'."\r\n\t\t".'<img style="float:left; padding:10px" alt="Cover Art" src="'.$iconurl.'">'."\r\n\t\t".'<div class="favoritestitle" style="padding:10px;width:100%"><strong>';
$titles = explode("/", $title);
$objectrow .= $titles[0].'</strong></a>';
if($author !=''){
$authors = explode(";", $author);	
$objectrow .=  '<br />'.$authors[0];
}
if($publisher !='') $objectrow .=  '<br />'.$publisher;
if($publisher2 !='') $objectrow .=  '<br />'.$publisher2;
if($ispartof !='') $objectrow .=  '<br />'.$ispartof;
if($pubdate !='') $objectrow .=  ', '.$pubdate.' ';
$objectrow .=  "\r\n\t\t".'</div>'."\r\n\t".'</div>'."\r\n";	

//Create RSS output
$rssrow .= '<item>'."\r\n";
$rssrow .= '<title><![CDATA['.$title.']]></title>'."\r\n";
//$rssrow .= '<media:content medium="image" url="'.$iconurl.'" />';
$itemURL = 'https://'.$cdomain.'.primo.exlibrisgroup.com/discovery/fulldisplay?docid='.$rawrecordid.'&context='.$context.'&vid='.$cvid.'&lang=en&search_scope='.$cscope.'&adaptor='.$adaptor.'&tab='.$ctab.'';
$onesearchurl = urlencode($itemURL);
$rssrow .= '<link>'.$weblocation.'l.php?l='.$onesearchurl.'</link>'."\r\n";
//$rssrow .= '<link><![CDATA['.$onesearchurl.']]></link>'."\r\n";
$rssrow .= '<description><![CDATA['."\r\n";
if($author !='') $rssrow .= $author.'<br />';
if($publisher !='') $rssrow .= $publisher.' ';
if($publisher2 !='') $rssrow .= $publisher2.' ';
if($ispartof !='') $rssrow .= $ispartof.' ';
if($pubdate !='') $rssrow .= ''.$pubdate.' ';
$rssrow .= ']]></description>'."\r\n";
$rssrow .= '<guid isPermaLink="false">'.$recordid.'</guid>'."\r\n";
$rssrow .= '</item>'."\r\n";	
	
	
//var_dump($response);	
}
}
if($i >=1) {
$totalresults ='Yes';
$cachefile= $directorypath.'files/'.$id.'.html'; // generate the static html file to this location
ob_start(); 
if($listTitle !='')
echo '<h3 class="favoritestitle">'.$listTitle.'</h3>'."\r\n";
echo '<div class="favoriteswrap" style="display: flex; flex-wrap: wrap; align-items: stretch; margin-right: -15px; margin-left: -15px;">'."\r\n";
echo $objectrow;
echo '</div>';
$fp = fopen($cachefile,'w');
fwrite($fp,ob_get_contents());
fclose($fp);

$rsscachefile= $directorypath.'files/'.$id.'.rss'; // generate the static html file to this location
	
ob_start(); 
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
echo '<channel>';
echo '<atom:link href="'.$weblocation.'files/'.$id.'.rss" rel="self" type="application/rss+xml" />'."\r\n";
echo '<title><![CDATA['.$listTitle.' Reading List]]></title>'."\r\n";
echo '<link>'.$weblocation.'files/'.$id.'.rss</link>'."\r\n";
echo '<description>A list of recommended readings</description>';
echo $rssrow;
echo '</channel>';
echo '</rss>';
$rssfp = fopen($rsscachefile,'w');
fwrite($rssfp,ob_get_contents());
fclose($rssfp);
}
else
$totalresults = 'No';
header("Location: yourlist.php?id={$id}&result={$totalresults}");

?>
