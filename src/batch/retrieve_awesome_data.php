<?php

/****************************
 * This script hits the Awesome API to retrieve the n recently awesomed items.
 * We grab the hollis ID of each awesome item and use it to get details of the item from the 
 * LibraryCloud API. We package that up and serialize it ot a JSON object that is used display
 * a StackView stack on the homepage.
 ****************************/


    $sl_home = dirname(dirname(dirname(__FILE__)));
    require_once ($sl_home . '/etc/sl_ini.php');

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => "http://librarylab.law.harvard.edu/awesome/api/item/recently-awesome?limit=200"
    ));
    $response = curl_exec($curl);
    curl_close($curl);

    $awesome_api_response = json_decode($response);

    // For each Awesome item, let's get details from the LC API
    $static_docs = array();
    
    foreach ($awesome_api_response->docs as $doc) {
                
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://librarycloud.harvard.edu/v1/api/item/?filter=id_inst:' . $doc->hollis_id
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        $lc_response = json_decode($response);
        
        if (count($lc_response->docs) === 1) {        
            // Do we need to set default like this, or does StackView do that for us?
            $static_doc = array('title' => 'Uknown Title', 'creator' => array(), 'measurement_page_numeric' => 0, 'measurement_height_numeric' => 0, 'pub_date' => 0);

            // The labels in LC and the Awesome API don't always match. Let's align those here.        
            if (property_exists($lc_response->docs[0], 'title') && !empty($lc_response->docs[0]->title)) {
                $static_doc['title'] = $lc_response->docs[0]->title;
            }
        
            if (property_exists($lc_response->docs[0], 'creator') && !empty($lc_response->docs[0]->creator)) {
                $static_doc['creator'] = $lc_response->docs[0]->creator;
            }
        
            if (property_exists($lc_response->docs[0], 'pages_numeric') && !empty($lc_response->docs[0]->pages_numeric)) {
                $static_doc['measurement_page_numeric'] = $lc_response->docs[0]->pages_numeric;
            }
        
            if (property_exists($lc_response->docs[0], 'height_numeric') && !empty($lc_response->docs[0]->height_numeric)) {
                $static_doc['measurement_height_numeric'] = $lc_response->docs[0]->height_numeric;
            }
        
            if (property_exists($lc_response->docs[0], 'shelfrank') && !empty($lc_response->docs[0]->shelfrank)) {
                $static_doc['shelfrank'] = $lc_response->docs[0]->shelfrank;
            }
        
            if (property_exists($lc_response->docs[0], 'pub_date_numeric') && !empty($lc_response->docs[0]->pub_date_numeric)) {
                $static_doc['pub_date'] = $lc_response->docs[0]->pub_date_numeric;
            }
            
            if (property_exists($lc_response->docs[0], 'format') && !empty($lc_response->docs[0]->format)) {
                $static_doc['format'] = $lc_response->docs[0]->format;
            }

            $static_doc['link'] = "$www_root/item/" . $lc_response->docs[0]->title_link_friendly . '/' . $lc_response->docs[0]->id;

            $static_docs[] = $static_doc;
        }
    }
    
    $complete_object = array();
    $complete_object['start'] = -1;
    $complete_object['limit'] = 0;
    $complete_object['num_found'] = count($static_docs);
    $complete_object['docs'] = $static_docs;
    $serialized_object = json_encode($complete_object);

    // Let's make sure we have at least 10 items and then we'll write them out to a static JSON file
    if ($complete_object['num_found'] > 10) {
        $file_path = $sl_home . '/src/web/js/awesome.json';
        // Write the contents back to the file
        file_put_contents($file_path, $serialized_object);
    }

?>