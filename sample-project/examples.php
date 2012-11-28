<?php
//This is just some config with public and secret keys for UC.
require_once 'config.php';
//requesting lib for PHP 5.3/5.4
require_once '../uploadcare/lib/5.3-5.4/Uploadcare.php';
//using namespace
use \Uploadcare;

//create object istance for Api. 
$api = new Uploadcare\Api(UC_PUBLIC_KEY, UC_SECRET_KEY);

/**
 * Let's start with widgets.
 * You can get widget url by using this:
 * */
print $api->widget->getJavascriptUrl()."\n";

/**
 * You can just use method below to get all the code to insert widget
 */
print $api->widget->getInclude()."\n";

/**
 * Or just this method to print
 */
$api->widget->printInclude()."\n";

/**
 * Ok, lets do some requests. This is request to index (http://api.uploadcare.com).
 * This will return an stdClass with information about urls you can request.
 */
$data = $api->request(API_TYPE_RAW);

/**
 * Lets request account info.
 * This will return just some essential data inside stdClass such as: username, pub_key and email
 */
$account_data = $api->request(API_TYPE_ACCOUNT);

/**
 * Ok, now lets get file list.
 * This request will return stdClass with all files uploaded and some information about files.
 * Each files has:
 *  - size
 *  - upload_date
 *  - last_keep_claim
 *  - on_s3
 *  - made_public
 *  - url
 *  - is_image
 *  - file_id
 *  - original_filename
 *  - removed
 *  - mime_type
 *  - original_file_url
 *  
 */
$files_raw = $api->request(API_TYPE_FILES);

/**
 *  Previous request is just some raw request and it will return raw data from json.
 *  There's a better way to handle all the files by using method below.
 *  It will return an array of \Uploadcare\File objects to work with.
 *  
 *  This objects don't provide all the data like in previous request, but provides ways to display the file 
 *  and to use methods such as resize, crop, etc 
 */
$files = $api->getFileList();

/**
 * If you have a file_id (for example, it's saved it your database) you can create object for file easily.
 * Just user request below
 */
$file_id = $files[0]->getFileId();
$file = $api->getFile($file_id);

/**
 * Ok, using object of \Uploadcare\File class we can get url for the file
 */
echo $file->getUrl()."\n";

/**
 * Now let's do some crop.
 */
$width = 400;
$height = 400;
$is_center = true;
$fill_color = 'ff0000';
echo $file->crop($width, $height, $is_center, $fill_color)->getUrl()."\n";

/**
 * And here's some resize with width and height
 * */
echo $file->resize($width, $height)->getUrl()."\n";

/**
 * Width only
 */
echo $file->resize($width)->getUrl()."\n";

/**
 * Height only
 */
echo $file->resize(false, $height)->getUrl()."\n";

/**
 * We can also use scale crop
 */
echo $file->scaleCrop($width, $height, $is_center)->getUrl()."\n";

/**
 * And we can apply some effects.
 */
echo $file->applyFlip()->getUrl()."\n";
echo $file->applyGrayscale()->getUrl()."\n";
echo $file->applyInvert()->getUrl()."\n";
echo $file->applyMirror()->getUrl()."\n";

/**
 * We can apply more that one effect!
 * */
echo  $file->applyFlip()->applyInvert()->getUrl()."\n";

/**
 * We can combine operations, not just effects.
 * 
 * Just chain methods and finish but calling "getUrl()".
 * 
 * */
echo $file->resize(false, $height)->crop(100, 100)->applyFlip()->applyInvert()->getUrl()."\n";

/**
 * The way you provide operations matters.
 * We can see the same operations below, but result will be a little bit different.
 */
echo $file->crop(100, 100)->resize(false, $height)->applyFlip()->applyInvert()->getUrl()."\n";

/**
 * Ok, it's everything with operations.
 * Let's have some fun with uploading files.
 * First of all, we can upload file from url. Just use construction below.
 * This will return File instance.
 */
$file = $api->uploader->fromUrl('http://www.baysflowers.co.nz/Images/tangerine-delight.jpg');
$status = $api->uploader->status($file->getFileId());

/**
 * File must be uploaded, but it's not stored yet. 
 * Let's store it.
 * We user true flag to be sure that file is uploaded.
 **/
try {
	$file->store(true);	
} catch (Exception $e) {
	echo $e->getMessage()."\n";
	echo nl2br($e->getTraceAsString())."\n";	
}

/**
 * We can do any operations with this file now.
 **/
echo $file->applyFlip()->getUrl()."\n";

/**
 * We can upload file from path
 * */
$file = $api->uploader->fromPath(dirname(__FILE__).'/test.jpg');
$file->store();
echo $file->applyFlip()->getUrl()."\n";

/**
 * Or even just use a file pointer.
 **/
$fp = fopen(dirname(__FILE__).'/test.jpg', 'r');
$file = $api->uploader->fromResource($fp);
$file->store();
echo $file->applyFlip()->getUrl()."\n";

/**
 * The last thing you can do is upload a file just from it's contents. But you will have to provide 
 * mime-type.
 */
$content = "This is some text I want to upload";
$file = $api->uploader->fromContent($content, 'text/plain');
$file->store();
echo $file->getUrl()."\n";