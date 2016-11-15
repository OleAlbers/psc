<?php
/**
 * Plugin Name: PSC Reader
 * Version: 0.0.1
 * Plugin URI: https://github.com/OleAlbers/psc
 * Description: Reads informations from PSC (podlove simple chapters)-files and displays them as html-content
 * Author: Ole Albers
 * Author URI: http://dotnet.work
 * License: MIT
 */

 /*
 Usage: [PSC]http://cdn.podseed.org/blathering/blathering_011.psc[/PSC]
 */

 $pscClass="entry-meta";
 $pscHeader="Pingbacks:";
 $pscUrlsOnly=true;



/**
 * Add PSC-Content to current article
 */
function replace_psc($post_id, $post ) {
    if (isset($post->post_status) && 'auto-draft' == $post->post_status) {
        return;
    }

    $content = $post->post_content;
    $pscStart=strpos($content,"[PSC]");
    $pscEnd=strpos($content,"[/PSC]");
    if ($pscStart>=0 && $pscEnd>$pscStart) {
        $filename=substr($content,$pscStart+5, $pscEnd-$pscStart-5);
        $content=str_replace("[PSC]".$filename."[/PSC]",read_psc($filename),$content);
        remove_action('save_post', 'replace_psc');
        wp_update_post(array('ID' => $post_id, 'post_content' => $content));
        add_action('save_post', 'replace_psc');
    }
}

function read_psc($feed_url) {
    global $pscClass;
    global $pscHeader;
    global $pscUrlsOnly;

    $hasContent=false;
    $returnValue="";
    $pscContent = file_get_contents($feed_url);
    $xml = new SimpleXmlElement($pscContent);
    $chapters=$xml->children('psc', true);

    $tmpContent="<div class='".$pscClass."'><span class='pscHeader'>".$pscHeader."</span><br/>";
    foreach ($chapters as $chapter) {
	    $attr=$chapter->attributes();

	     $url=$attr->href;
	    $title=$attr->title;
	    $start=$attr->start;

        if (!$pscUrlsOnly  || isset($url)) {
	        $hasContent=true;
    		$tmpContent.=("<div class='pscContent'>".$start." <a href='".$url."'>".$title."</a></div>");
    	}
    }
    $tmpContent.= "</br></br></div>";
    if ($hasContent) {
        return $tmpContent;
    }
    return null;
}


add_action('save_post', 'replace_psc', 100, 2);


?>
