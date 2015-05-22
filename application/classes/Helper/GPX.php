<?php

    class Helper_GPX
    {
			protected $id = null;
			protected $dir = 'assets/uploads/hike/';
			
			public function __construct($id)
			{
				$this->id = $id;
				$this->dir .= $id.'/';
			}
			
			protected function ensureDirectory()
			{
				if (!file_exists('assets/uploads/hike'))
				{
					mkdir('assets/uploads/hike', 0777);
				}
				
				if (!file_exists('assets/uploads/hike/'.$this->id))
				{
					mkdir('assets/uploads/hike/'.$this->id, 0777);
				}
			}
			
			public function uploadTempFile($tmpPath)
			{
				$hike = ORM::factory('Hike', $this->id);
				
				if ($hike->loaded())
				{
					$this->ensureDirectory();
					
					$time = time();
					$gpxFile = $this->dir . $time . '.gpx';
					move_uploaded_file($tmpPath, $gpxFile);
					$hike->gpx = $gpxFile;
					$hike->save();
					
					$kml = self::gpxToKml($gpxFile);
					$kmlFile = $this->dir . $time . '.kml';
					file_put_contents($kmlFile, $kml);
					$hike->kml = $kmlFile;
					$hike->save();
				}
				else
				{
					throw new Exception('Hike not found');
				}
			}
			
			public static function gpxToKml($path)
			{
				$z = new XMLReader;
				$z->open($path);

				$doc = new DOMDocument;
				
				$points = self::parsePoints($z, $doc);
				
				$kml = '<?xml version="1.0" encoding="UTF-8"?>
							<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">
							<Document>
								<name>snow_lake_path.kml</name>
								<Style id="lineStyle">
									<LineStyle>
										<color>99ffac59</color>
										<width>6</width>
									</LineStyle>
								</Style>
								<Placemark>
									<name>Path</name>
									<styleUrl>#lineStyle</styleUrl>
									<LineString>
										<tessellate>1</tessellate>
										<coordinates>';

				foreach ($points as $point)
				{
					$kml .= $point['lon'].','.$point['lat'].','.$point['ele'].' ';
				}
				
				$kml .= '</coordinates>
								</LineString>
							</Placemark>
						</Document>
						</kml>';
						
				return $kml;
						
			}
			
		protected static function parsePoints(XMLReader $z, DOMDocument $doc)
		{
			// move to the first <product /> node
			while ($z->read() && $z->name !== 'trkpt');

			$points = [];
			// now that we're at the right depth, hop to the next <product/> until the end of the tree
			while ($z->name === 'trkpt')
			{
				// either one should work
				//$node = new SimpleXMLElement($z->readOuterXML());
				$node = simplexml_import_dom($doc->importNode($z->expand(), true));

				// now you can use $node without going insane about parsing
				// var_dump($node->time);
				// var_dump($node['lat']);
				$point = [
						'lat' => (string)$node['lat'],
						'lon' => (string)$node['lon'],
						'ele' => (string)$node->ele,
						'time' =>(string) $node->time,
					];
				
				array_push($points, $point);

				// go to next <product />
				$z->next('trkpt');
			}
			
			// echo json_encode($points );
			// var_dump($points);
			return $points;
		}
		
		protected static function parseTrackSegment(XMLReader $z, DOMDocument $doc)
		{
			// move to the first <product /> node
			while ($z->read() && $z->name !== 'trkseg');

			$segments = [];
			
			// now that we're at the right depth, hop to the next <product/> until the end of the tree
			while ($z->name === 'trkseg')
			{
				// either one should work
				//$node = new SimpleXMLElement($z->readOuterXML());
				$node = simplexml_import_dom($doc->importNode($z->expand(), true));

				// now you can use $node without going insane about parsing
				
				array_push($segments, self::parsePoints($z, $doc));

				// go to next <product />
				$z->next('trkseg');
			}
			
			return $segments;
		}
		
		protected static function parseTrack(XMLReader $z, DOMDocument $doc)
		{
			// move to the first <product /> node
			while ($z->read() && $z->name !== 'trk');

			$tracks = [];
			
			// now that we're at the right depth, hop to the next <product/> until the end of the tree
			while ($z->name === 'trk')
			{
				// either one should work
				//$node = new SimpleXMLElement($z->readOuterXML());
				$node = simplexml_import_dom($doc->importNode($z->expand(), true));

				// now you can use $node without going insane about parsing
				
				array_push($tracks, self::parseTrackSegment($z, $doc));

				// go to next <product />
				$z->next('trk');
			}
		}
    }