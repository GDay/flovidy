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
        $i = 0;
        $remove[] = "'";
        $remove[] = '"';
        $remove[] = '%22';
        $remove[] = '%20';
        foreach ($urls as $url) {
            $this->urls[$i] = str_replace($remove, "", $url);
            $i++;
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

                $parts = explode(';', $url);
                foreach($parts as $part){
                    preg_match("/B0(?:(?!\/).)*/", $part, $number);
                    $finalUrl = $finalUrl . '&ASIN.'.$i.'='.$number[0].'&Quantity.'.$i.'=1';
                    $i++;
                }
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
                    $oldUrl = $this->get_referral_url($oldUrl);
                }
                $url = $this->create_new_url($oldUrl);   
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

    function get_referral_url($oldUrl){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $oldUrl);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_NOBODY, true); 
        curl_exec($ch);
        return curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    }

    function create_new_url($oldUrl){
        $url = strtr($oldUrl,array("amazon.com"  => "amazon.".$this->country));
        $httpcode = 405;
        while ($httpcode == 405){
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_TIMEOUT,10);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5');
            $output = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpcode == 500){
                wp_die();
            }
        }
        // if page does not exist in new store
        if(strpos($output, '<title>404')!== false){
            if(strpos($url, 'keywords')!== false){
                $parts = parse_url($url);
                parse_str($parts['query'], $query);
                $url = 'https://www.amazon.'.$this->country.'/s/?keywords='.$query['keywords'];
            } else {
                $pieces = explode('/', $url);
                if(strtolower($pieces[3]) == 'dp'){
                    $url = 'https://www.amazon.'.$this->country.'/s/?keywords='.strtr($pieces[4], array("-"  => "+"));
                } else {
                    $url = 'https://www.amazon.'.$this->country.'/s/?keywords='.strtr($pieces[3], array("-"  => "+"));
                }
            }
        }

        return $url;
    }



} 