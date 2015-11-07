<?php

$con=mysqli_connect("localhost","root","","web_crawler");
if(mysqli_connect_errno())
{
	die("Error connecting to database");
}

$to_crawl="http://www.starsports.com";
$c=array();
$t=array();
$flags=array();
$index=0;

function get_info($input){
	$title = preg_match('!<title.*>(.*?)</title>!i', $input, $match) ? $match[1] : '';
	if(empty($title))
		$title="no title";
	return $title;
}

function get_links($url){
	global $c;
	global $t;
	global $con;
	global $flags;
	global $index;
	
	$input=@file_get_contents($url);
	
	$regexp="<a\s[^>]*href=(\"??)([^\">]*?)\\1[^>]*>(.*)<\/a>";
	preg_match_all("/$regexp/siU", $input, $matches);
	
	$re="<meta\s+name=\"(.*)\"\s+content=\"(.*)\"\s*\/?>";
	preg_match_all("/$re/siU", $input,$meta);
/*
	$re_nohtml="\<(.+)(\s*(.*=.*))*\/*>\/";
	$input1=preg_replace("/$regexp/siU", "", $input);
	$words=explode(" ", $input1);
	$noisy_words=mysqli_query($con,"SELECT noisy_words FROM stop_word_table");
	$noise=array();
	while($row=mysqli_fetch_assoc($noisy_words))
	{
		array_push($noise, $row['noisy_words']);
	}
	$flag=0;
	for($i=0;$i<count($words);$i++){
		for($j=0;$j<count($noise);$j++){
			if($words[$i]==$noise[$j]){
				$flag=1;
				break;
			}
		}
		if($flag==0){
			$query_run=mysqli_query($con,"INSERT INTO word_table VALUES('','".mysql_real_escape_string($words[$i])."')");
		}
	}*/
	$des='';
	$keyword='';
	for($i=0;$i<count($meta[1]);$i++){
	if($meta[1][$i]=="description")
		$des=$meta[2][$i];
	if($meta[1][$i]=="keywords")
		$keyword=$meta[2][$i];
	}
	
	$base_url=parse_url($url,PHP_URL_HOST);
	
	$l=$matches[2];
	
	$title=get_info($input);
	mysqli_query($con,"INSERT INTO data VALUES('','".mysql_real_escape_string($title)."','".mysql_real_escape_string($des)."','".mysql_real_escape_string($keyword)."','".mysql_real_escape_string($url)."' )");
	foreach ($l as $link) {
		if(strpos($link, "#")){
			$link=substr($link,0,strpos($link,"#"));
		}
		if(substr($link,0,1)=="."){
			$link=substr($link,1);
		}
		if(substr($link,0,7)=="http://"||substr($link,0,8)=="https://"){
			$link=$link;
		} else if(substr($link,0,2)=="//"){
			$link=substr($link,2);
		} else if(substr($link,0,1)=="#"){
			$link=$url;
		} else if(substr($link,0,7)=="mailto:"){
			$link="[".$link."]";
		} else{
			if(substr($link,0,1)!="/"){
				$link=$base_url."/".$link;
			} else{
				$link=$base_url.$link;
			}
		}

		if(substr($link,0,7)!="http://"&& substr($link,0,8)!="https://" && substr($link,0,1)!="["){
			if(substr($url,0,8)=="https://"){
				$link="https://".$link;
			} else{
				$link="http://".$link;
			}
		}

		//echo $link."<br>";
		if(!in_array($link, $c)){
			array_push($c,$link);
			array_push($t, $title);
			mysqli_query($con,"INSERT INTO links VALUES('".mysql_real_escape_string($url)."','".mysql_real_escape_string($link)."' )");
			

		}
	}
	$flags[$index++]=1;
}

get_links($to_crawl);

// foreach ($c as $page) {
/*$LEN=count($c);
$flag=0;
for($i=0;$i<$LEN&&$flag<10;$i++){
	get_links($c[$i]);
	if($LEN==count($c))
		$flag++;
	$LEN=count($c);
	//var_dump($c);
	//echo $LEN."<br><br>";
	error_log("hello");
}*/

//var_dump($c);
echo "<br>";
for($i=0;$i<5;$i++)
	if(!empty($c)){
		get_links($c[$i]);
		//var_dump($c);
		echo "<br>";
	}

/*foreach ($c as $page) {
	echo $page."<br>";
}*/

//print_r($flags);
echo "<br><br>";
error_log('count='.count($c));
for($i=0, $count = count($c);$i<$count;$i++) {
 echo $c[$i]."<br>";
 echo $t[$i]."<br><br>";
}

?>