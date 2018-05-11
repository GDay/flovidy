<?php


include_once('Flovidy_LifeCycle.php');
include_once('Flovidy_Links.php');
include_once('Flovidy_Location.php');

class Flovidy_Plugin extends Flovidy_LifeCycle {

    public function getOptionMetaData() {
        return array(
            'us_ref' => array(__('USA referral code', 'flovidy')),
            'in_ref' => array(__('India referral code', 'flovidy')),
            'jp_ref' => array(__('Japan referral code', 'flovidy')),
            'fr_ref' => array(__('French referral code', 'flovidy')),
            'de_ref' => array(__('Germany referral code', 'flovidy')),
            'it_ref' => array(__('Italy referral code', 'flovidy')),
            'es_ref' => array(__('Spain referral code', 'flovidy')),
            'uk_ref' => array(__('UK referral code', 'flovidy')),
            'ca_ref' => array(__('Canada referral code', 'flovidy')),
            'br_ref' => array(__('Brazil referral code', 'flovidy')),
            'cn_ref' => array(__('China referral code', 'flovidy')),
            'au_ref' => array(__('Australia referral code', 'flovidy')),
            'bitly_access_token' => array(__('Bitly API key', 'flovidy')),
        );
    }

    protected function initOptions() {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr > 1)) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
    }
    public function upgrade() {
    }

    public function addActionsAndFilters() {
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));
        function add_flovidy_scripts()
        {
            wp_enqueue_script( 'get_flovidy_urls', plugins_url('/js/localize.js', __FILE__), array('jquery'), false, true );
            wp_localize_script( 'get_flovidy_urls', 'myAjax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' )
            ));
            wp_enqueue_script('get_flovidy_urls');
        }
        add_action( 'wp_enqueue_scripts', 'add_flovidy_scripts' );            
        add_action('wp_ajax_get_flovidy_urls', 'get_flovidy_urls');
        add_action('wp_ajax_nopriv_get_flovidy_urls', 'get_flovidy_urls');
        function add_tag($url, $orRef, $newRef){
            if(strpos($url, $orRef) !== false){
                $new_link = strtr($url,array($orRef  => $newRef));
            } else if(strpos($url, "?") !== false){ 
                $new_link = $url. "&tag=".$newRef;
            } else{
                $new_link = $url. "?tag=".$newRef;
            }
            return $new_link;
        }
        function get_flovidy_urls() {
            $records = [];
            $urls = $_POST['urls'];
            $location = new Flovidy_Location();
            $location->get_client_ip();
            $location->get_country();
            $location->get_referral_tag();
            $newRef = $location->getNewRef();
            $country = $location->getCountry();
            $link = $location->getLink();
            $orRef = trim(get_option("Flovidy_Plugin_us_ref"));

            // Check if we need to dig in the database
            if ($newRef == "" || $country == 'us'){
                foreach($urls as $url){
                    if(strpos($url, "amzn.to") === false){ 
                        // is this an add to cart thing?
                        if(strpos($url, ";") !== false){
                            $i = 1;
                            $finalUrl = 'https://www.amazon.com/gp/aws/cart/add.html?AssociateTag='.$newRef;
                            $parts = explode(';', $url);
                            foreach($parts as $part){
                                preg_match("/B0(?:(?!\/).)*/", $part, $number);
                                $finalUrl = $finalUrl . '&ASIN.'.$i.'='.$number[0].'&Quantity.'.$i.'=1';
                                $i++;
                            }
                            $item = array(us_link => $url, new_link=>$finalUrl);                                
                        } else {
                            $item = array(us_link => $url, new_link=>add_tag($url, $orRef, $newRef));
                        }
                    } else {
                        $item = array(us_link => $url, new_link=>$url);
                    }
                    array_push($records, $item);
                }
                print_r(json_encode($records));
                wp_die();
            }
            $links = new Flovidy_Links($urls, $country, $link, $newRef);
            $records = $links->create_shopping_chart();
            $newRecords = $links->create_new_links();
            foreach ($newRecords as $record){
                $item = array(us_link => $record['us_link'], new_link=>add_tag($record[$link], $orRef, $newRef));
                array_push($records, $item);
            }
            print_r(json_encode($records));
            wp_die();
        }
    }


}
