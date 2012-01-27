<?php
/*
 *		This Source Code Form is subject to the terms 
 *		of the Mozilla Public License, v. 2.0. If a copy 
 *		of the MPL was not distributed with this file, 
 *		You can obtain one at http://mozilla.org/MPL/2.0/.
 *
 * 		Copyright (c) 2012 Balazs Pete, UCD Science Society
 */


/*
 *		Alternative name for convertLinks
 *		@param text text to be parsed
 *		@return parsed and modified input string
 */
function linkify($text){
	return convertLinks($text);
}

/*
 *		Concerts links to anchors (<a>), if YouTube or Imgur link attaches info for embedding (calls parseForImgurImage, parseForYoutubeVideo and parseURL on input)
 *		@param text text to be parsed
 *		@return parsed and modified input string
 */
function convertLinks($text){
	$text = parseForImgurImage($text);
	$text = parseForYoutubeVideo($text);
	$text = parseURL($text);
	
	return $text;
}

/*
 *		Concerts links to anchors (<a>)
 *		@param text test to be parsed
 *		@return parsed and modified input string
 */
function parseURL($text){
	$protocol = array();
	preg_match("/(http|https|ftp|sftp|telnet|mailto)\:\\/\\//i",$text,$protocol);
	
	$regex = '/((((?<!\"|>|href=|href\s=\s|href=\s|href\s=|src=|src\s=\s|src=\s|src\s=)((https?|s?ftp|telnet|mailto)\:\\/\\/))|(?<=\s))([\d\w-^\">]+\.)*?[\d\w-]+\.([a-z]{2,4}\.)*([a-z]{2,4})((\\/)([\d\w\\/\.\?\*\+_=;%&-])*)?)(?!(\w))/i';
	$text = preg_replace($regex, ' <a href="'.($protocol ? '' : 'http://' ).'$1" target="_new" class="parsedlink">$1</a>', " ".$text);
	return $text;
}

/*
 *		Searches input text for a YouTube link, if found extracts videoID and attaches as data-videoid attribute
 *		@param text text to be parsed
 *		@return parsed and modified input text
 */
function parseForYoutubeVideo($text){
	$regex = '/(?<!\"|>|href=|href\s=\s|href=\s|href\s=|src=|src\s=\s|src=\s|src\s=)((((https?|s?ftp|telnet|mailto)\:\\/\\/))([\d\w^\">]+\.)*?(youtube\.com|youtu\.be)(\\/)?([\d\w\\/\.\?\*\+_=;%&-])*)/i';
	$regex2 = "v=[\d\w]+/i";
	
	if(preg_match($regex,$text,$match)){	
		$parsed =  parse_url($match[0]);
		$parsed_query = convertUrlQuery($parsed['query']);
		return $text = preg_replace($regex, '<a href="$1" target="_new" class="parsedlink youtubelink" data-videoid="'.$parsed_query['v'].'">$1</a>', $text);
	} else {
		return $text;
	}
}

/*
 *		Searches for an Imgur link, if found retrieves image data and attaches it as data- attributes
 *		@param text text to be parsed
 *		@return parsed and modified string
 */
function parseForImgurImage($text){
	$regex = '/(?<!\"|>|href=|href\s=\s|href=\s|href\s=|src=|src\s=\s|src=\s|src\s=)((((https?|s?ftp|telnet|mailto)\:\\/\\/))([\d\w^\">]+\.)*?(imgur\.com)(\\/)?([\d\w\\/\.\?\*\+_=;%&-])*)/i';
	$regex_small = '/(((imgur.com.*\\/))([a-zA-Z0-9]+)(?=(\.[\w]+)?))/i';
	$matches = array();
	if(preg_match($regex_small,$text,$matches)){
		try {
			$data = json_decode(file_get_contents('http://api.imgur.com/2/image/'.$matches[4].'.json'));
			if($data){
				return $text = preg_replace($regex, '<a href="$1" target="_new" class="parsedlink imgurlink" title="'.$data->image->image->title.'" imghash="'.$matches[4].'" data-original="'.$data->image->links->original.'" data-small_square="'.$data->image->links->small_square.'" data-large_thumbnail="'.$data->image->links->large_thumbnail.'" data-imgur_page="'.$data->image->links->imgur_page.'" data-width="'.$data->image->image->width.'" data-height="'.$data->image->image->height.'">$1</a>', $text);
			} else {
				return $text;
			}
		} catch (Exception $e){
			return $text;
		}
	} else {
		return $text;
	}
}


/*
 * 		Converts a url query (string) into an array
 *		@param query query string
 *		@return query array
 */
function convertUrlQuery($query) { 
    $queryParts = explode('&', $query); 
    
    $params = array(); 
    foreach ($queryParts as $param) { 
        $item = explode('=', $param); 
        $params[$item[0]] = $item[1]; 
    } 
    
    return $params; 
} 

/*
 *		Looks for emoticon shortcuts and converts them into html tags (images applied with CSS)
 *		@param text text to be parsed
 *		@return parsed string
 */
function iconify($text){
	$text = ' '.$text;
	$text = preg_replace("/(?<=\s)(:-\)|:\)|:\]|=\)|\(=|\[:|\(:|\(-:)(?=(\s|\Z))/i",'<span title="$1" class="emoticon happy">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(:\(|:-\(|\):|\)-:)(?=(\s|\Z))/i",'<span title="$1" class="emoticon sad">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(:P|:p|:-P|:-p)(?=(\s|\Z))/i",'<span title="$1" class="emoticon tongue">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(:-D|:D|=D|:-d|:d|=d)(?=(\s|\Z))/i",'<span title="$1" class="emoticon grin">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(:-O|:O|:-o|:o|o:|o-:|O:|O-:)(?=(\s|\Z))/i",'<span class="emoticon gasp">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(;-\)|;\)|\(;|\(-;)(?=(\s|\Z))/i",'<span title="$1" class="emoticon wink">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(>:\(|>:-\(|\):<|\)-:<)(?=(\s|\Z))/i",'<span title="$1" class="emoticon grumpy">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(:-\\/|:\\/)(?=(\s|\Z))/i",'<span title="$1" class="emoticon unsure">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(:'\(|\)':)(?=(\s|\Z))/i",'<span title="$1" class="emoticon cry">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(:-\\*|:\\*|\\*:|\\*-:)(?=(\s|\Z))/i",'<span title="$1" class="emoticon kiss">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(\^_\^)(?=(\s|\Z))/i",'<span title="$1" class="emoticon kiki">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(-_-)(?=(\s|\Z))/i",'<span title="$1" class="emoticon squint">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(o\.O|O\.o|ooOO|OOoo)(?=(\s|\Z))/i",'<span title="$1" class="emoticon confuse">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(>:-O|>:O|>:-o|>:o)(?=(\s|\Z))/i",'<span title="$1" class="emoticon upset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(:v)(?=(\s|\Z))/i",'<span title="$1" class="emoticon pacman">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(:3)(?=(\s|\Z))/i",'<span title="$1" class="emoticon cutecat">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(8-\)|8\)|B-\)|B\)|\(-8|\(8)(?=(\s|\Z))/i",'<span title="$1" class="emoticon glasses">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(8-\\||8\\||B-\\||B\\||\\|8|\\|-8)(?=(\s|\Z))/i",'<span title="$1" class="emoticon sunglasses">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(O:-\)|O:\)|\(:O|\(-:O)(?=(\s|\Z))/i",'<span title="$1" class="emoticon angel">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(3:-\)|3:\))(?=(\s|\Z))/i",'<span title="$1" class="emoticon devil">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	$text = preg_replace("/(?<=\s)(<3)(?=(\s|\Z))/i",'<span title="$1" class="emoticon heart">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$text);
	//$text = preg_replace("/(?<=\s)()(?=(\s|\Z))/i",'<span class="emoticon grin">&nbsp;&nbsp;&nbsp;</span>',$text);
	return $text;
}









?>