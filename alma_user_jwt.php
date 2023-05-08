<?PHP
$key=''; //from EL developers network, stored in database or hidden directory
//key requires Primo USER JWT (Read/Write access)
//from Primo Search URL
$vid = '01CALS_SDL:01CALS_SDL'; // Primo view name
$institution ='01CALS_SDL'; // Primo institution ID
$userName=''; //Alma Primary Identifier - stored in database or sent from list.php page
$userGroup ='Faculty'; // Alma User Group

$url = 'https://api-na.hosted.exlibrisgroup.com/primo/v1/userJwt?apikey='.$key;
$data = '{
   "viewId":"'.$vid.'",
   "institution":"'.$institution.'",
   "language":"en_US",
   "userName": '.$userName.',
   "userGroup":'.$userGroup.',
   "onCampus":"false"
}';
$options = array(
    'http' => array(
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => $data
    )
);
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
$json = json_encode($result);
echo $result;
?>
