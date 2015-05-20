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
			<h2 class="post-title">Snow Lake Trail</h2>
			<h3 class="post-subtitle">Snoqualmie Pass</h3>
	</div>
</div>
<div class="row">
	<div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
		<div id="map-canvas" style="height: 400px;"></div>
	</div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true"></script>

<script>
function initialize() {
  var coord = new google.maps.LatLng(47.445767, -121.425493);
  var mapOptions = {
    zoom: 13,
    center: coord,
	mapTypeId: google.maps.MapTypeId.TERRAIN
  }

  var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

  var ctaLayer = new google.maps.KmlLayer({
    url: 'http://step.polymtl.ca/~alexrose/hike/snow_lake_path.kml'
  });
  ctaLayer.setMap(map);
  
  var marker = new google.maps.Marker({
    position: new google.maps.LatLng(47.457431, -121.454825), 
    map: map,
    title: 'Image'
  });
  
  var contentString = '<img src="img.jpg" width="300" style=" -ms-transform: rotate(180deg);  -webkit-transform: rotate(180deg); transform: rotate(180deg);"/>';
  
   var infowindow = new google.maps.InfoWindow({
      content: contentString,
      maxWidth: 350
  });
  
  google.maps.event.addListener(marker, 'click', function() {
	//$('#img').show();
	infowindow.open(map,marker);
  });
}

google.maps.event.addDomListener(window, 'load', initialize);

    </script>