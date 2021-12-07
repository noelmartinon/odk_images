<?php
/*
 * Description: Display image from blob stores in ODK Aggregate and ODK Central databases
 * Version: 1.0.0
 * Author: Noël MARTINON
 * Licence: GPLv3
 */

$pdo_array = require 'connect.php';

// ODK Aggregate variables
$blobKey = $_GET["blobKey"];
$uuid = $_GET["uuid"];

// ODK Central variables
$blobId = $_GET["blobId"];
$photo = $_GET["photo"];

// Check the variables
if (empty($blobKey) && empty($blobId)) exit(0);
if (isset($blobKey) && empty($uuid)) exit(0);
if (isset($blobId) && empty($photo)) exit(0);

// Create sql query for ODK Aggregate
if (isset($blobKey)) {
    $pdo = $pdo_array['aggregate'];
    $sql = "SELECT BLB.\"VALUE\",BN.\"UNROOTED_FILE_PATH\" FROM \"".$aggregate_schema."\".\"".$blobKey."_BLB\" AS BLB
    INNER JOIN aggregate.\"".$blobKey."_REF\" AS REF ON REF.\"_SUB_AURI\"=BLB.\"_URI\"
    INNER JOIN aggregate.\"".$blobKey."_BN\" AS BN ON BN.\"_URI\"=REF.\"_DOM_AURI\"
    WHERE BLB.\"_URI\"='uuid:".$uuid."';";
}
// Create sql query for ODK Central
else {
    $pdo = $pdo_array['central'];
    $sql = "SELECT \"content\" FROM \"".$central_schema."\".\"blobs\" WHERE \"id\"='".$blobId."';";
}

// Prepare and execute query
$stmt = $pdo->prepare($sql);
$stmt->execute();
$num = $stmt->rowCount();

// Display image
if ($num) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // Aggregate
    if (isset($blobKey)) {
        $Content_Disposition = "filename=".pathinfo($row['UNROOTED_FILE_PATH'])['filename'].".jpg";
        $Content_type = "image/jpeg";
        $data = $row['VALUE'];
    }
    // Central
    else {
        $Content_Disposition = "filename=".$photo;
        $file_parts = pathinfo($photo);
        if ($file_parts['extension'] == "jpg" || $file_parts['extension'] == "jpeg")
            $Content_type = "image/jpeg";
        else if ($file_parts['extension'] == "png")
            $Content_type = "image/png";
        $data = $row['content'];
    }

    // HTML headers
    header("Content-Disposition: ".$Content_Disposition);
    header("Content-type: ".$Content_type);

    /*
    // Display original image without autorotate
    // NOTA: An 'echo stream_get_contents($data);' displays the original image containing EXIF informations
    if ($autorotate !== TRUE) {
        echo stream_get_contents($data);
        exit(0);
    }
    */

    // Display image without EXIF informations
    $exif = exif_read_data ("data://".$Content_type.";base64,".base64_encode($data), 0, true);
    $image = imagecreatefromstring(stream_get_contents($data));
    if ($autorotate === TRUE)
        $rotate = imagerotate($image, array_values([0, 0, 0, 180, 0, 0, -90, 0, 90])[$exif['IFD0']['Orientation'] ?: 0], 0);
    else $rotate = $image;

    if($Content_type == 'image/jpeg')
        imagejpeg($rotate, null, $jpeg_quality);
    else
        imagepng($rotate, null, $png_quality);

    imagedestroy($image);
    imagedestroy($rotate);
}
