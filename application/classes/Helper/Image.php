<?php

   class Helper_Image
   {
       public static function writeImage($model, $id, &$response, $thumb = false)
       {
           $obj = ORM::factory($model, $id);
           if ($thumb && file_exists('assets/uploads/'.strtolower($model).'/thumbs/'.$id.'.jpg'))
           {
               $file = 'assets/uploads/'.strtolower($model).'/thumbs/'.$id.'.jpg';
               $response->headers('Content-Type', 'image/jpg');
           }
           else if ($obj->image == 'JPG' && file_exists('assets/uploads/'.strtolower($model).'/'.$id.'.jpg'))
           {
               $file = 'assets/uploads/'.strtolower($model).'/'.$id.'.jpg';
               self::rotate($file);
               $response->headers('Content-Type', 'image/jpg');
           }
           else if ($obj->image == 'PNG' && file_exists('assets/uploads/'.strtolower($model).'/'.$id.'.png'))
           {
               $file = 'assets/uploads/'.strtolower($model).'/'.$id.'.png';
               $response->headers('Content-Type', 'image/png');
           }
           else
           {
               // We dont want the default bee anymore
//               $file = 'assets/img/aep.png';
//               $response->headers('Content-Type', 'image/png');

               $data['success'] = false;

               echo json_encode($data);
           }

           if (isset($file))
           {
              self::writeImageFile($file, $response);
           }
       }

       public static function writeImageFile($file, $response)
       {
           ob_clean();

           $fp = fopen($file, 'rb');

           $response->headers('Content-Length', filesize($file));

           fpassthru($fp);
       }

       public static function deleteImage($model, $id)
       {
           $obj = ORM::factory($model, $id);

           if ($obj->image != 'NONE' && file_exists('assets/uploads/'.strtolower($model).'/'.$id.'.'.strtolower($obj->image)))
           {
               $file = 'assets/uploads/'.strtolower($model).'/'.$id.'.'.strtolower($obj->image);
               unlink($file);
           }

           if ($obj->image != 'NONE' && file_exists('assets/uploads/'.strtolower($model).'/thumbs/'.$id.'.jpg'))
           {
               $file = 'assets/uploads/'.strtolower($model).'/thumbs/'.$id.'.jpg';
               unlink($file);
           }
       }

       public static function hash($model, $id)
       {
           $obj = ORM::factory($model, $id);
           if ($obj->image == 'JPG' && file_exists('assets/uploads/'.strtolower($model).'/'.$id.'.jpg'))
           {
               $file = 'assets/uploads/'.strtolower($model).'/'.$id.'.jpg';
           }
           else if ($obj->image == 'PNG' && file_exists('assets/uploads/'.strtolower($model).'/'.$id.'.png'))
           {
               $file = 'assets/uploads/'.strtolower($model).'/'.$id.'.png';
           }

           $hash = '';

           if (isset($file))
           {
               $data = file_get_contents($file);
               $hash = md5(base64_encode($data));
           }

           return $hash;
       }



       // This fixes the orientation of phone pictures
       public static function rotate($image)
       {
           $exif = exif_read_data($image);

           if (array_key_exists('Orientation', $exif))
           {
               $jpg = imagecreatefromjpeg($image);
               $orientation = $exif['Orientation'];
               switch ($orientation)
               {
                   case 3:
                       $jpg = imagerotate($jpg, 180, 0);
                       break;
                   case 6:
                       $jpg = imagerotate($jpg, -90, 0);
                       break;
                   case 8:
                       $jpg = imagerotate($jpg, 90, 0);
                       break;
                   default:
                       return;
               }
               imagejpeg($jpg, $image, 90);
           }
       }

       // http://stackoverflow.com/questions/1855996/crop-image-in-php
       public static function crop($path, $width, $height, $output)
       {
           $type = strtolower(pathinfo($path)['extension']);
           if ($type == 'jpg')
           {
               $image = imagecreatefromjpeg($path);
           }
           else if ($type == 'png')
           {
               $image = imagecreatefrompng($path);
           }
           else
           {
               return false;
           }

           $originalWidth = imagesx($image);
           $originalHeight = imagesy($image);

           $original_aspect = $originalWidth / $originalHeight;
           $thumb_aspect = $width / $height;

           if ( $original_aspect >= $thumb_aspect )
           {
               $new_height = $height;
               $new_width = $originalWidth / ($originalHeight / $height);
           }
           else
           {
               $new_width = $width;
               $new_height = $originalHeight / ($originalWidth / $width);
           }

           $thumb = imagecreatetruecolor($width, $height);

           imagecopyresampled($thumb,
               $image,
               0 - ($new_width - $width) / 2, // Center the image horizontally
               0 - ($new_height - $height) / 2, // Center the image vertically
               0, 0,
               $new_width, $new_height,
               $width, $height);
           imagejpeg($thumb, $output, 80);

           return true;
       }

       public static function thumb($path, $width, $height, $output)
       {
           $thumb = new Imagick();
           $thumb->readImage($path);

           $size['columns'] = $thumb->getImageWidth();
           $size['rows'] = $thumb->getImageHeight();

           if ($size['columns'] <= $width && $size['rows'] <= $height)
           {
               $thumb->writeImage($output);
               $thumb->clear();
               $thumb->destroy();
               return;
           }

           if ($size['columns'] >= $size['rows'])
           {
               $height = (int) ($size['rows'] / $size['columns'] * $width);
           }
           else
           {
               $width = (int) ($size['columns'] / $size['rows'] * $height);
           }

           $thumb->resizeImage($width,$height,Imagick::FILTER_LANCZOS,1);
           $thumb->writeImage($output);
           $thumb->clear();
           $thumb->destroy();
       }
   }