<?php

   class Helper_Image
   {
       public static function writeImage($name, $id, &$response, $thumb = false)
       {
		   $thumb = 'assets/uploads/hike/'.$id.'/thumbs/'.$name;
		   $computed = 'assets/uploads/hike/'.$id.'/computed/'.$name;
           if ($thumb && file_exists($thumb))
           {
               $file = $thumb;
               $response->headers('Content-Type', 'image/jpg');
           }
		   else if ($thumb && file_exists($computed))
		   {
			   self::thumb($computed, 1920, 1200, $thumb);
			   $file = $thumb;
		   }
           else if (!$thumb && file_exists($computed))
           {
               $file = $computed;
               $response->headers('Content-Type', 'image/jpg');
           }
           else
           {
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
       public static function rotate($baseURL, $name, $image)
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
               }
               imagejpeg($jpg, $baseURL.'computed/'.$name, 90);
           }
		   else
		   {
			   copy($image, $baseURL.'computed/'.$name);
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
	   
	   public static function extractLocation($image)
	   {
		   $path = 'assets/uploads/hike/'.$image->hikeID.'/orig/'.$image->name;
           $exif = exif_read_data($path);
		  

           if (array_key_exists('GPSLatitudeRef', $exif) && array_key_exists('GPSLatitude', $exif))
           {
               $ref = $exif['GPSLatitudeRef'];
               $latitude = $exif['GPSLatitude'];
			   
			   $degree = self::splitGPS($latitude[0]);
			   $minute = self::splitGPS($latitude[1]);
			   $second = self::splitGPS($latitude[2]);
			   
			   $lat = $degree + ($minute / 60) + ($second / 3600);
			   
			   if (strtolower($exif['GPSLatitudeRef']) == 's')
			   {
				   $lat *= -1;
			   }
			   
			   $image->lat = $lat;
		   }
		   
		   if (array_key_exists('GPSLongitudeRef', $exif) && array_key_exists('GPSLongitude', $exif))
           {
               $ref = $exif['GPSLongitudeRef'];
               $longitude = $exif['GPSLongitude'];
			   
			   $degree = self::splitGPS($longitude[0]);
			   $minute = self::splitGPS($longitude[1]);
			   $second = self::splitGPS($longitude[2]);
			   
			   $lon = $degree + ($minute / 60) + ($second / 3600);
			   
			   if (strtolower($exif['GPSLongitudeRef']) == 'w')
			   {
				   $lon *= -1;
			   }
			   
			   $image->lon = $lon;
		   }
		   
		   $image->save();
	   }
	   
	   protected static function splitGPS($coord)
	   {
			$parts = explode('/', $coord);

			  if(count($parts) <= 0)// jic
				return 0;
			  if(count($parts) == 1)
				return $parts[0];

			  return floatval($parts[0]) / floatval($parts[1]);
	   }
   }