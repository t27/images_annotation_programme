<?php

include 'xmlVocAnnotations.php';
include 'configuration.php';
date_default_timezone_set('Asia/Kolkata');
$obj = json_decode($_POST["sendInfo"]);

function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    if ($needle === '') {
        return true;
    }
    $diff = \strlen($haystack) - \strlen($needle);
    return $diff >= 0 && strpos($haystack, $needle, $diff) !== false;
}

$file = 'file.log';
file_put_contents($file, "INFO - Synthesis of last submit\n");
file_put_contents($file, date('l jS \of F Y h:i:s A')."\n",FILE_APPEND | LOCK_EX);
file_put_contents($file, serialize($obj)."\n",FILE_APPEND | LOCK_EX);
$user = gethostbyaddr($_SERVER['REMOTE_ADDR']);

$folder = $obj->folder;
$id     = $obj->id;
$width  = $obj->width;
$height = $obj->height;
$source = $obj->source;

$annotations = $obj->{'annotations'};

file_put_contents($file, "Annotations = ".sizeof($annotations)."\n",FILE_APPEND | LOCK_EX);

$imageSize = [  "width"  => $width ,
				"height" => $height,
				"depth"  => 3 ];			
	
$user_tag = $source."|".$user;

if(endsWith($source,$user))
{
	$user_tag=$source;
}

$xml = new xmlVocAnnotations($folder, $id, $imageSize, $user_tag);

file_put_contents($file, "xmlVocAnnotations created\n",FILE_APPEND | LOCK_EX);

foreach ($annotations as &$annotation)
{						
	$xml->addBndBox($annotation->x,
					$annotation->y,
					$annotation->width,
					$annotation->height,
					$annotation->tag);
}

file_put_contents($file, "Before saving\n",FILE_APPEND | LOCK_EX);
// Write xml to file

// $xml_file_name= pathinfo($id, PATHINFO_FILENAME).".xml"; 
// $xml_path = $folder.$xml_file_name
$xml->save($IMAGE_ROOT_DIR."/".$folder);

$response_array['status']  = 'success'; /* match error string in jquery if/else */ 
$response_array['message'] = $id.".xml has been created.";   /* add custom message */ 

file_put_contents($file, "End of file validationTagsAndRegions" ,FILE_APPEND | LOCK_EX);
file_put_contents($file, " " ,FILE_APPEND | LOCK_EX);
file_put_contents($file, " " ,FILE_APPEND | LOCK_EX);

header('Content-type: application/json');
echo json_encode($response_array);

?>