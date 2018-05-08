<?php 
class Flovidy_Location { 
    
    public $country = ""; 
    public $ipaddress; 
    public $link = "";
    public $newRef = "";
    function __construct() {
       $this->country = "us";
   	}
    
    function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        $this->ipaddress = $ipaddress;
    }

    function get_country() {
        $file = plugins_url('/GeoIP.dat', __FILE__);
        include("geoip.inc");
        $gi = geoip_open(__DIR__.'/GeoIP.dat', GEOIP_MMAP_CACHE);
        $country = geoip_country_code_by_addr($gi, $this->ipaddress);
        if ($country == false){
            $country = 'us';
        } else if ($country == 'US'){
            $country = 'us';
        } else if ($country == 'IN'){
            $country = 'in';
        } else if ($country == 'JP'){
            $country = 'co.jp';
        } else if ($country == 'FR'){
            $country = 'fr';
        } else if ($country == 'DE'){
            $country = 'de';
        } else if ($country == 'IT'){
            $country = 'it';
        } else if ($country == 'ES'){
            $country = 'es';
        } else if ($country == 'GB'){
            $country = 'co.uk';
        } else if ($country == 'CA'){
            $country = 'ca';
        } else if ($country == 'BR'){
            $country = 'com.br';
        } else if ($country == 'CN'){
            $country = 'cn';
        } else if ($country == 'AU'){
            $country = 'com.au';
        } else {
        	$country = 'us';
        }
        $this->country = $country;
    }

    function get_referral_tag(){
    	if ($this->country == 'co.uk'){
            $this->link = 'uk_link';
            $this->newRef = trim(get_option("Flovidy_Plugin_uk_ref"));
        } elseif ($this->country == 'co.jp'){
            $this->link = 'jp_link';
            $this->newRef = trim(get_option("Flovidy_Plugin_jp_ref"));
        } elseif ($this->country == 'com.br'){
            $this->link = 'br_link';
            $this->newRef = trim(get_option("Flovidy_Plugin_br_ref"));
        } elseif ($this->country == 'com.au'){
            $this->link = 'au_link';
            $this->newRef = trim(get_option("Flovidy_Plugin_au_ref"));
        } else {
            $this->link = $this->country.'_link';
            $this->newRef = trim(get_option("Flovidy_Plugin_" .$this->country. '_ref'));
        }
    }
    function getNewRef(){
        return $this->newRef;
    }
    function getCountry(){
        return $this->country;
    }
    function getLink(){
        return $this->link;
    }
} 