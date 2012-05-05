<?

// Go to https://developers.facebook.com/apps to create our app & copy/paste APP_ID & APP_SECret

define("PAGE_ID", 44902596242); // The Page you want to backup
define("APP_ID", "428789773798154"); // The App-Secret
define("APP_SECRET", "3b0fb0f21b46d92dab6c4677894750ec"); // The App-Secret

// Optional: Run script as user with admin-permissions, shouldnt make a difference, since photos are all public, 
// but might be important f. geo- or age-restricted pages nonetheless! Go to http://developers.facebook.com/tools/explorer to 
// get your access-token with "manage_pages"-permission

//define("ACCESS_TOKEN", "AAAGFZB2YmiwoBABF5vNHoAY2xBRBxp4tmvH2Maf1Gz0awWkFOeWs7OJdehDKnuYaUF5tb8ZANdtBWG0KJEqmZAcGFtB1S3WnqFLDVDdiQZDZD");

include_once("facebook/facebook.php");

include_once("facebook/facebook.php");

$facebook = new Facebook(array("appId" => APP_ID, "secret" => APP_SECRET, "cookie" => true));

if (defined(@ACCESS_TOKEN)) {
  $facebook->setAccessToken(ACCESS_TOKEN);

  // Get all Pages the user has admin-perms
  $pages = $facebook->api("/me/accounts");

  // Search the one page you want to backup
  $page = null;
  foreach ($pages["data"] as $p) {
    if ($p["id"] == PAGE_ID) {
      $page = $p;
    }
  }

  if (!$page)
    die("Seems you don't have admin-privilege on the requested page!\n");
}

// Create a backup-dir
$backup_dir = "backup_".PAGE_ID."_".date("Ymd-His");
mkdir ($backup_dir);

// Get Albums of Page
$album_count = 0;
$photo_count = 0;
$albums = $facebook->api("/".PAGE_ID."/albums?access_token=".@$p["access_token"]);
foreach ($albums["data"] as $album) {

  // Create a sub-dir f. album
  $album_dir = $backup_dir."/".$album["name"];
  mkdir($album_dir);
  $album_count++;

  $photos = $facebook->api("/".$album["id"]."/photos?access_token=".@$p["access_token"]);
  while (@$photos["data"]) {
    foreach (@$photos["data"] as $photo) {

      $ext = substr($photo["source"],strrpos($photo["source"],"."));
      $photo_filename = $album_dir."/".$photo["id"]."-".date("Ymd-Hmi",strtotime($photo["created_time"])).$ext;
      file_put_contents($photo_filename, file_get_contents($photo["source"]));
      print $photo_filename." (".$photo["width"]."x".$photo["height"].")\n";
      $photo_count++;
    }

    if (@$photos["paging"]["next"]) {      
      $photos = $facebook->api(str_replace("https://graph.facebook.com","",$photos["paging"]["next"]));      
    } else {
      $photos = null;
    }
  }
}

print "\nStored ".$album_count." with ".$photo_count." Photos!\n\n";
@die;

?>