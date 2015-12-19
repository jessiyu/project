<?php
$target_dir = "uploads/";
$compressrate = $_POST["compressrate"];
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
}
// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}
// Check file size
if ($_FILES["fileToUpload"]["size"] > 500000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}
// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
//echo $_POST["compressrate"];
echo shell_exec("python /home/ubuntu/project/compressimage.py $compressrate $target_file");
echo shell_exec("cp $target_file /var/www/downloads");

$downloadfile = '/var/www/downloads/'.$_FILES["fileToUpload"]["name"];
$filename = $_FILES["fileToUpload"]["name"];

if(!$downloadfile){ // file does not exist
    die('file not found');
} else {
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=$filename");
    header("Content-Transfer-Encoding: binary");
    ob_clean();
    flush();
    // read the file from disk
    readfile($downloadfile);
}
// Create MySQL login values and

// set them to your login information.

$username = "YourUserName";

$password = "YourPassword";

$host = "localhost";

$database = "binary";


// Make the connect to MySQL or die

// and display an error.

$link = mysql_connect($host, $username, $password);

if (!$link) {

die('Could not connect: ' . mysql_error());

}

// Select your database
mysql_select_db ($database);

// Make sure the user actually

// selected and uploaded a file

if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['size'] > 0) {


// Temporary file name stored on the server

$tmpName = $_FILES['fileToUpload']['tmp_name'];


// Read the file

$fp = fopen($tmpName, 'r');

$data = fread($fp, filesize($tmpName));

$data = addslashes($data);

fclose($fp);



// Create the query and insert

// into our database.

$query = "INSERT INTO tbl_images ";

$query .= "(fileToUpload) VALUES ('$data')";

$results = mysql_query($query, $link);


// Print results

print "Thank you, your file has been uploaded.";


}

else {

print "No image selected/uploaded";

}


// Close our MySQL Link

mysql_close($link);

echo shell_exec("rm $target_file");
echo shell_exec("rm /var/www/downloads/$filename");


?>
