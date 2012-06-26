<?php

    $image_file = 'puzzle.jpg';

    $src = imagecreatefromjpeg($image_file);
    list($width, $height, $type, $attr) = getimagesize($image_file);

    $split_size = '150';

    $cal_width  = $width % $split_size;
    $cal_height = $height % $split_size;



    if ($cal_width > 0) {
        $new_width = intval($width / $split_size) * $split_size + 100;
    } else {
        $new_width = $width;
    }
    if ($cal_height > 0) {
        $new_height = intval($height / $split_size) * $split_size + 100;
    } else {
        $new_height = $height;
    }

    if ($width > 1200) {
        $width = 1200;
        $new_width = 1200;
    }




    $image_p = imagecreatetruecolor($new_width, $new_height);
    $image = imagecreatefromjpeg($image_file);


    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    imagejpeg($image_p, $image_file, 100);



    $x_comp = intval($new_width / $split_size);
    $y_comp = intval($new_height / $split_size);


    $winning_string = '';
    $image_names = '';

    $src = imagecreatefromjpeg($image_file);
    $dest = imagecreatetruecolor($split_size, $split_size);

    for ($y = 0; $y < $y_comp; $y++) {
        for ($i = 0; $i < $x_comp; $i++) {
            $characters = 'abcdefghijklmnopqrstuvwxyz';
            $ran_string = '';
            for ($p = 0; $p < 4; $p++) {
                $ran_string .= $characters[mt_rand(0, strlen($characters) - 1)];
            }

            imagecopy($dest, $src, 0, 0, $i * $split_size, $y * $split_size, $split_size, $split_size);
            imagejpeg($dest, "images/$ran_string.jpg");

            $winning_string .= $ran_string;
            $image_names .= $ran_string . ",";
        }
    }

    
    $image_names = substr($image_names, 0, -1);
    echo "<p style='font-size:30px;color:red'>Your Puzzle Wining String - ".$image_names."</p>";
    $images = explode(',', $image_names);
    shuffle($images);
    $images = implode(",",$images);
    echo "<p style='font-size:30px;color:blue'>Your Puzzle Load String - " . $images."</p>";
?>
