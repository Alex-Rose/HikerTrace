<?php

    class Controller_AdminUpload extends Controller_Async
    {
        public function before()
        {
            parent::before();
        }
		
		public function action_create()
		{
			$name = $this->request->post('name');
			$desc = $this->request->post('description');
			
			$hike = ORM::factory('Hike');
			$hike->name = $name;
			$hike->description = $desc; 
			$hike->save();
			
			$this->data['success'] = true;
			
			$view = View::factory('upload');
			$view->set('id', $hike->pk());
			$this->data['feedback'] = $view->render();
		}
		
		public function action_gpx()
		{
			
			$id = $this->request->param('id');
			$tempName = $_FILES['file']['tmp_name'];
			// $contents = file_get_contents($tempName);
	
	
			if ($id != null)
			{				
				// var_dump($contents);
				
				// var_dump($_FILES);
				
				$gpx = new Helper_GPX($id);
				$gpx->uploadTempFile($tempName);
				
				$this->data['success'] = true;
				
				$view = View::factory('upload_image');
				$view->set('id', $id);
				$this->data['feedback'] = $view->render();

			}
		
			// var_dump($kml);
			
			// echo htmlspecialchars($kml);
		}
		
		public function action_image()
		{
			
			$id = $this->request->param('id');
			$tempName = $_FILES['image']['tmp_name'];
			$name = $_FILES['image']['name'];
			// $contents = file_get_contents($tempName);
			$hike = ORM::factory('Hike', $id);
	
			if ($id != null && $hike->loaded())
			{			
				$basePath = 'assets/uploads/hike/'.$id.'/';
				$origPath = $basePath.'orig/'.$name;
				$computed = $basePath.'computed/'.$name;
				
				if (!file_exists($basePath.'orig'))
				{
					mkdir($basePath.'orig', 0777);
				}
				if (!file_exists($basePath.'computed'))
				{
					mkdir($basePath.'computed', 0777);
				}
				if (!file_exists($basePath.'thumbs'))
				{
					mkdir($basePath.'thumbs', 0777);
				}
				
				move_uploaded_file($tempName, $origPath);
				
				Helper_Image::rotate($basePath, $name, $origPath);
				
				$image = ORM::factory('Image')->where('name', '=', $name)->where('hikeID', '=', $hike->pk())->find();
				$image->name = $name;
				$image->hikeID = $hike->pk();
				$image->save();
				
				Helper_Image::extractLocation($image);
				
				$this->data['success'] = true;
			}
		}
		
		
		
		
    }
