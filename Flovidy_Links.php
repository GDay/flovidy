<?php 
class Flovidy_links { 
    
    public $urls; 
    public $orRef; 
    public $stringifiedUrls;
    public $newRef;
    public $records;
    public $link;
    private $results;
    
    function __construct($urls, $country, $link, $newRef) {
        $this->country = $country;
        $this->urls = $urls;
        $this->link = $link;
        $this->newRef = $newRef;
        $remove[] = "'";
        $remove[] = '"';
        $remove[] = '%22';
        $remove[] = '%20';
        foreach ($urls as $url) {
            $this->urls[$i] = str_replace($remove, "", $url);
        }
        $this->stringifiedUrls = "'". implode( "','", $urls) ."'";
        global $wpdb;
        $this->results = $wpdb->get_results('SELECT us_link, '.$link.' FROM '.$wpdb->prefix . 'ai_link WHERE us_link in ('.$this->stringifiedUrls.')', OBJECT );
   	}
    function create_shopping_chart() {
        $j=0;
        $rec = [];
        $temp_urls = $this->urls;
        foreach($temp_urls as $url){
            if(strpos($url, ";") !== false){
                $i = 1;
                if($this->country != 'us'){
                    $finalUrl = 'https://www.amazon.'. $this->country.'/gp/aws/cart/add.html?AssociateTag='.$this->newRef;
                } else {
                    $finalUrl = 'https://www.amazon.com/gp/aws/cart/add.html?AssociateTag='.$this->newRef;
                }
                preg_match("/B0(?:(?!\/).)*/", $url, $number);
                $finalUrl = $finalUrl . '&ASIN.'.$i.'='.$number[0].'&Quantity.'.$i.'=1';
                $i++;
                $item = array(us_link => $url, new_link=>$finalUrl);
                array_push($rec, $item);
                unset($temp_urls[$j]);
                
            } 
            $j++;
        }
        $this->urls = $temp_urls;
        $this->records = $rec;
        return $rec;

    }
    function create_new_links(){
        $records = [];
        foreach ($this->urls as $oldUrl) {
            $orUrl = $oldUrl;
            $item = [];
            $exists = True; 
            $item = $this->link_exists($oldUrl);
            // if it does not, create the array and say it doesn't exist
            if(!$item){
                $item = array(us_link => $oldUrl, new_link=>'');
                $exists = False;
            }
            // it if does, check if new link exists
            $current_link = $this->link;
            if($item->$current_link){
                $new_item = array(us_link => $item->us_link, $current_link => $item->$current_link);
                array_push($records, $new_item);
            } else {
                if(strpos($oldUrl, "amzn.to") !== false){
                    $oldUrl = $this->get_redirect_url($oldUrl);
                    if ($oldUrl == false) {
                        continue;
                    }
                }
                // rebuild link and remove all extra ids and tokens
                $newUrl = $this->rebuild_url($oldUrl);
                // change domain and check if url exists
                $url = $this->create_new_url($newUrl);
                // create a new record if not exists, else just update record with new url
                global $wpdb;
                if(!$exists){
                    $wpdb->insert( 
                        $wpdb->prefix .'ai_link', 
                        array( 
                            us_link => $orUrl, 
                            $this->link => $url
                        ), 
                        array( '%s', '%s' ) 
                    );
                } else {
                    $wpdb->update( 
                        $wpdb->prefix .'ai_link', 
                        array( 
                            $this->link => $url
                        ), 
                        array( 'us_link' => $orUrl ), 
                        array( '%s' ), 
                        array( '%s' ) 
                    );
                };
                $newRecord = array(us_link  => $orUrl, $this->link => $url);
                array_push($records, $newRecord);
            }
        
        }
        return $records;
    }
    function link_exists($oldUrl){
        // check if us_links exist
        foreach($this->results as $result){
            if($oldUrl == $result->us_link){
                return $result;
            }
        }
        return False;
    }

    function get_redirect_url($oldUrl){
        $url = 'https://api-ssl.bitly.com/v3/expand?access_token='.trim(get_option("Flovidy_Plugin_bitly_access_token")).'&shortUrl='.urlencode($oldUrl);
        $response =  wp_remote_retrieve_body(wp_remote_get($url));
        $response_json = json_decode(trim($response), true);
        if ($response_json['status_txt'] == 'OK') {
            return $response_json['data']['expand'][0]['long_url'];
        }
        return false;
    }

    function rebuild_url($oldUrl) {
        $index = 0;
        $code = '';
        preg_match("/B0(?:(?!\/).)*/", $oldUrl, $number);
        if (count($number) > 0) {
            $code = $number[0];
        }
        $parts = parse_url($oldUrl);
        parse_str($parts['query'], $query);
        if($code == ''){
			$newUrl = 'https://www.amazon.com/s/';
		} else {
			$newUrl = 'https://www.amazon.com/dp/'.$code.'/';
		}
        if(strpos($oldUrl, 'keywords')!== false){
			if(strpos($oldUrl, 'field-keywords')!== false){
				$newUrl .= '?keywords='.$query['field-keywords'];
			} else {
				$newUrl .= '?keywords='.$query['keywords'];
			}
            $newUrl = strtr($newUrl,array(" "  => "+"));
        }
        return $newUrl;
    }

    function create_new_url($oldUrl){
        $url = strtr($oldUrl,array("amazon.com"  => "amazon.".$this->country));
        if (strpos($url, '/s/?keywords=')){
			return $url; 
		}
        $counter = 0;
        $response = wp_remote_get($url,
            array(
                'timeout'    => 20,
                'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36',
            )
        );
        $httpcode = wp_remote_retrieve_response_code($response);
        // 500 generally means we are blocked or Amazon is having interal issues. No point in doing more requests.
        if ($httpcode == 500){
            wp_die();
        }
        // if page does not exist in new store
        if($httpcode == 404 || $httpcode == 504){
            if(strpos($url, 'keywords')!== false){
                $parts = parse_url($url);
                parse_str($parts['query'], $query);
                $url = 'https://www.amazon.'.$this->country.'/s/?keywords='.$query['keywords'];
            } else {
                preg_match("/B0(?:(?!\/).)*/", $url, $number);
                if (count($number) > 0) {
                    $code = $number[0];
                }
                $url = 'https://www.amazon.'.$this->country.'/s/?keywords='.strtr($code, array("-"  => "+"));
            }
        }
        return $url;
    }
} 