<style>
.post-title {
  font-size: 30px;
  margin-top: 30px;
  margin-bottom: 10px;
}
.post-subtitle {
  margin: 0;
  font-weight: 300;
  margin-bottom: 10px;
}
</style>
<div class="row">
	<div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
			<h2 class="post-title"><?php echo $hike->name;?></h2>
			<h3 class="post-subtitle"><?php echo $hike->description;?></h3>
	</div>
</div>
<div class="row">
	<div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
		<div id="map-canvas" style="height: 400px;" data-url="<?php echo URL::site($hike->kml, true, true);?>" data-lat="<?php echo $hike->lat;?>" data-lon="<?php echo $hike->lon;?>"></div> 
	</div>
</div>

<?php 
	$images = $hike->image->find_all();
	
	foreach ($images as $image)
	{
		echo '<div class="row">';
		echo '<div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">';
		echo '<img class="hike-img" src="'.URL::site('assets/uploads/hike/'.$hike->pk().'/computed/'.$image->name).'" data-lat="'.$image->lat.'" data-lon="'.$image->lon.'" style="max-width:500px" hidden />';
		echo '</div>';
		echo '</div>';
	}
	?>

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true"></script>

<script>
function initialize() {
  var lat = $('#map-canvas').attr('data-lat');
  var lon = $('#map-canvas').attr('data-lon');
  var coord = new google.maps.LatLng(lat, lon);
  var mapOptions = {
    zoom: 13,
    center: coord,
	mapTypeId: google.maps.MapTypeId.TERRAIN
  }

  var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

  var kml = $('#map-canvas').attr('data-url');
  var ctaLayer = new google.maps.KmlLayer({
    url: kml	
  });
  ctaLayer.setMap(map);
  
  $('img.hike-img').each(function(i, item){
	  
		  var lat = $(item).attr('data-lat');
		  var lon = $(item).attr('data-lon');
	  var marker = new google.maps.Marker({
		position: new google.maps.LatLng(lat, lon), 
		map: map,
		title: 'Image'+i
  });
  
  var contentString = '<img src="'+$(item).attr('src')+'" width="300" />';
  
   var infowindow = new google.maps.InfoWindow({
      content: contentString,
      maxWidth: 350
  });
  
  google.maps.event.addListener(marker, 'click', function() {
	//$('#img').show();
	infowindow.open(map,marker);
  });
  });
}

google.maps.event.addDomListener(window, 'load', initialize);

    </script>