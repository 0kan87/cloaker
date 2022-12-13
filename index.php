<?php
if ($_POST) {
	$baslangic = microtime(TRUE);

	$_POST['cloaker_install_domain'] = "localhost"; //vt de yoksa json hizmeti verme
	$_POST['fake_site'] = "https://hepsiburada.com";
	$_POST['success_site'] = "https://viptema.com";
	
	$ip 	   = $_POST["remote_addr"] == "::1" ? "" : "188.3.188.247";
	$ipkontrol = file_get_contents("http://ip-api.com/json/".$ip);
	$json      = json_decode($ipkontrol,true);

	function domainTemizle($domain){ // www. http:// https:// ve link sonundakileri temizler
		preg_match('@^(?:https:\/\/www\.|http:\/\/www\.|http://|https://)?([^/]+)@i', $domain, $matches);
		$host = $matches[1];
	    return $host;
	}

	function botEngel($user_agent) { //Kabul edilmeyen botları aşağı yazıyoruz
	  return isset($user_agent) && preg_match('/bot|crawl|slurp|spider/i', $user_agent) ? true : false;
	}

	function botEngelHost($remote_addr){
	    return preg_match('/facebook|google/i', gethostbyaddr($remote_addr)) ? true : false;
	}

	function dilEngel($http_accept_language) { //Kabul edilen dilleri aşağı yazıyoruz
	  return preg_match('/tr|en|az/i', substr($http_accept_language,0,2)) ? false : true;
	}

	function directGirisEngel($http_referer){ //Direct tarayıcıdan giriş yapılıyorsa yönlendirilir
	    return isset($http_referer) ? false : true;
	}

	function ulkeEngel() { //Ülke kodu yazılmayan ülkelerden giriş yapılmaz
	    global $json;
	    return in_array($json['countryCode'], array("TR","DE","AZ")) ? false : true;
	}

	function ispEngel() { //Google bazlı isp'leri engeller
	    global $json;
	    return preg_match('/Google/i', $json['isp']) ? true : false;
	}

	function googleAdsReferer($http_referer) { //Adwords geldiyse true değilse false
	    return isset($http_referer) and preg_match('/ads.google|googleadservices|source=web|aclk|pagead|search/i',$http_referer) ? true : false;
	}

	if (botEngel($_POST['http_user_agent']) === true) {
		// $cevap['response']['botEngel']['description'] = "Bot User Agent Tespiti Var";
		$cevap['response']['botEngel']['status'] = 'error';
	}else{
		// $cevap['response']['botEngel']['description'] = "Bot User Agent Tespiti Yok";
		$cevap['response']['botEngel']['status'] = 'success';
	}

	if (botEngelHost($_POST['remote_addr']) === true) {
		// $cevap['response']['botEngelHost']['description'] = "Bot Hosting Tespiti Var";
		$cevap['response']['botEngelHost']['status'] = 'error';
	}else{
		// $cevap['response']['botEngelHost']['description'] = "Bot Hosting Tespiti Yok";
		$cevap['response']['botEngelHost']['status'] = 'success';
	}

	if (dilEngel($_POST['http_accept_language']) === true) {
		// $cevap['response']['dilEngel']['description'] = "İzin Verilmeyen Dil Tespiti Var";
		$cevap['response']['dilEngel']['status'] = 'error';
	}else{
		$cevap['response']['dilEngel']['description'] = "İzin Verilmeyen Dil Tespiti Yok";
		$cevap['response']['dilEngel']['status'] = 'success';
	}

	if (directGirisEngel($_POST['http_referer']) === true) {
		// $cevap['response']['directGirisEngel']['description'] = "Direkt Giriş Tespiti Var";
		$cevap['response']['directGirisEngel']['status'] = 'error';
	}else{
		$cevap['response']['directGirisEngel']['description'] = "Direkt Giriş Tespiti Yok";
		$cevap['response']['directGirisEngel']['status'] = 'success';
	}

	if (ulkeEngel() === true) {
		// $cevap['response']['ulkeEngel']['description'] = "Bu Ülkeden Ziyaret Engellenmiş";
		$cevap['response']['ulkeEngel']['status'] = 'error';
	}else{
		// $cevap['response']['ulkeEngel']['description'] = "Bu Ülkeden Ziyaret Serbest";
		$cevap['response']['ulkeEngel']['status'] = 'success';
	}

	if (ispEngel() === true) {
		// $cevap['response']['ispEngel']['description'] = "İsp Engel Tespiti Var";
		$cevap['response']['ispEngel']['status'] = 'error';
	}else{
		$cevap['response']['ispEngel']['description'] = "İsp Engel Tespiti Yok";
		$cevap['response']['ispEngel']['status'] = 'success';
	}

	if (googleAdsReferer($_POST['http_referer']) == false) {
		// $cevap['response']['googleAdsReferer']['description'] = "Reklam Paneli Üzerinden Gelinmedi";
		$cevap['response']['googleAdsReferer']['status'] = 'success';
	}else{
		$cevap['response']['googleAdsReferer']['description'] = "Reklam Paneli Üzerinden Gelindi";
		$cevap['response']['googleAdsReferer']['status'] = 'error';
	}

	$cevap['redirect']['success_site'] = $_POST['success_site'];
	$cevap['redirect']['fake_site'] = $_POST['fake_site'];

	$bitis = microtime(TRUE);
   	$time = $bitis-$baslangic;
   	
   	$cevap['page_load'] = (float)number_format($time, 2, '.', '');

	if ($cevap['response']['botEngel']['status'] == 'success' and $cevap['response']['botEngelHost']['status'] == 'success' and $cevap['response']['dilEngel']['status'] == 'success' and $cevap['response']['directGirisEngel']['status'] == 'success' and $cevap['response']['ulkeEngel']['status'] == 'success' and $cevap['response']['ispEngel']['status'] == 'success' and $cevap['response']['googleAdsReferer']['status'] == 'success') {
		
		$cevap['code'] = 200;
	}else{
		$cevap['code'] = 404;
	}

	if ($cevap['code'] == 404) {
		echo json_encode($cevap);
		exit();
	}
	if($cevap['code'] == 200){
		echo json_encode($cevap);
		exit();
	}

}else{
	$cevap['response']['error'] = "Herhangi bir post isteğinde bulunmadınız!";
	echo json_encode($cevap);
	exit();
}