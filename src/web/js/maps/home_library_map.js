
var map, address;
var directionDisplay;
var directionsService;
var stepDisplay;
var markerArray = [];
var position;
var marker = null;
var polyline = null;
var poly2 = null;
var speed = 0.000005, wait = 1;
var infowindow = null;

var myPano;
var panoClient;
var nextPanoId;
var timerHandle = null;

function createMarker(latlng, label, html) {
// alert("createMarker("+latlng+","+label+","+html+","+color+")");
    var contentString = '<b>'+label+'</b><br>'+html;
    var marker = new google.maps.Marker({
        position: latlng,
        map: map,
        title: label,
        zIndex: Math.round(latlng.lat()*-100000)<<5,
        // animation: google.maps.Animation.BOUNCE,
        icon: 'http://google-maps-icons.googlecode.com/files/abduction.png'
		 });

     marker.myname = label;
        // gmarkers.push(marker);
    google.maps.event.addListener(marker, 'click', function() {
        infowindow.setContent(contentString);
        infowindow.open(map,marker);
        });
    return marker;
}







function initialize() {
  	infowindow = new google.maps.InfoWindow({
      size: new google.maps.Size(150,50)
    });
    // Instantiate a directions service.
    directionsService = new google.maps.DirectionsService();

    // Create a map and center it
    var myOptions = {
      zoom: 3,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

		/*
		if(navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(pos){
				var lat = pos.coords.latitude;
				var lng = pos.coords.longitude;
				address = new google.maps.LatLng(lat, lng);
				map.setCenter(address);
			}, function(){
				address = ' Lebanon, Kansas';
		    geocoder = new google.maps.Geocoder();
				geocoder.geocode( { 'address': address}, function(results, status) {
			       map.setCenter(results[0].geometry.location);
				});
			});
		}else{
		*/
			address = ' Lebanon, Kansas';
	    geocoder = new google.maps.Geocoder();
			geocoder.geocode( { 'address': address}, function(results, status) {
		       map.setCenter(results[0].geometry.location);
			});
		/*
		}
		*/
    // Create a renderer for directions and bind it to the map.
    var rendererOptions = {
      map: map
    }
    directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions);

    // Instantiate an info window to hold step text.
    stepDisplay = new google.maps.InfoWindow();

    polyline = new google.maps.Polyline({
			path: [],
			strokeColor: '#00FF00',
			strokeWeight: 3
    });
    poly2 = new google.maps.Polyline({
			path: [],
			strokeColor: '#FFFFFF',
			strokeWeight: 3
    });

}



var steps = []

var step = 50; // 5; // metres
var tick = 140; // milliseconds// milliseconds
var eol;
var k=0;
var stepnum=0;
var speed = "";
var lastVertex = 1;
var autoscrolling;

function showUpRoute(address, zoom, markerOps){
	var zoom = zoom || 13;
    var myOptions = {
		zoom: zoom,
		mapTypeId: google.maps.MapTypeId.ROADMAP
    };
	var markerOps = markerOps || {};
    var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	var address = address || ' Lebanon, Kansas';

    geocoder = new google.maps.Geocoder();
		geocoder.geocode( { 'address': address}, function(results, status) {
	       map.setCenter(results[0].geometry.location);
		   console.log("res geo", results[0]);
			var title = (markerOps.title) ? markerOps.title : "You need a title :)";
			var address = (markerOps.address) ? markerOps.address : address;
			var icon = (markerOps.icon) ? markerOps.icon : 'http://google-maps-icons.googlecode.com/files/library.png';

			var html;
			if(markerOps.html){
				html = markerOps.html
			}else{
				var infoDesc = (markerOps.desc) ? '<div id="bodyContent">'+ markerOps.desc + '</div>' : "";
				var infoTitle = (title) ? '<h1 id="firstHeading" class="firstHeading">' + title + '</h1>' : "";
				var infoAddress = (address) ? '<p id="mapAddress" class="mapAddress">' + address + '</p>' : "";
				var infoIcon = (icon) ? '<div id="siteNotice">'+ '<img src="'+ icon +'"></div>' : "";
				html = '<div id="content">'+ infoIcon + infoTitle + infoAddress + infoDesc + '</div>';
			}
			

			var infowindow = new google.maps.InfoWindow({
			    content: html
			});




			var marker = new google.maps.Marker({
			      position: results[0].geometry.location,
			      title: title,
				  icon: icon
			  });




			google.maps.event.addListener(marker, 'click', function() {
			  infowindow.open(map,marker);
			   setTimeout(function () { infowindow.close(); }, 5000);
			});

			marker.setMap(map);
	});
}


function calcRoute(starter, ender){
		if (timerHandle) { clearTimeout(timerHandle); }
		if (marker) { marker.setMap(null);}
		polyline.setMap(null);
		poly2.setMap(null);
		directionsDisplay.setMap(null);
    polyline = new google.maps.Polyline({
			path: [],
			strokeColor: '#FFFF00',
			strokeWeight: 3
    });
    poly2 = new google.maps.Polyline({
			path: [],
			strokeColor: '#00FFFF',
			strokeWeight: 3
    });
    // Create a renderer for directions and bind it to the map.
    var rendererOptions = {
      map: map
    }

		directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions);

    //var start = document.getElementById("start").value;
    //var end = document.getElementById("end").value;
		var travelMode = google.maps.DirectionsTravelMode.DRIVING

    var request = {
        //origin: start,
        //destination: end,
        origin: starter,
        destination: ender,
        travelMode: travelMode
    };

		// Route the directions and pass the response to a
		// function to create markers for each step.
	  directionsService.route(request, function(response, status) {
  	  if (status == google.maps.DirectionsStatus.OK){
				directionsDisplay.setDirections(response);
        var bounds = new google.maps.LatLngBounds();
        var route = response.routes[0];
        startLocation = new Object();
        endLocation = new Object();

        // For each route, display summary information.
				var path = response.routes[0].overview_path;
				var legs = response.routes[0].legs;
        for (i=0;i<legs.length;i++) {
          if (i == 0) {
            startLocation.latlng = legs[i].start_location;
            startLocation.address = legs[i].start_address;
            // marker = google.maps.Marker({map:map,position: startLocation.latlng});
            marker = createMarker(legs[i].start_location,"start",legs[i].start_address,"green");
          }
          endLocation.latlng = legs[i].end_location;
          endLocation.address = legs[i].end_address;
          var steps = legs[i].steps;
          for (j=0;j<steps.length;j++) {
            var nextSegment = steps[j].path;
            for (k=0;k<nextSegment.length;k++) {
              polyline.getPath().push(nextSegment[k]);
              bounds.extend(nextSegment[k]);
            }
          }
        }
        polyline.setMap(map);
        map.fitBounds(bounds);
//       createMarker(endLocation.latlng,"end",endLocation.address,"red");
				map.setZoom(18);
				var meters = response.routes[0].legs[0].distance.value;
				step = (meters/80) + 50; // 50 = 5; // metres
				startAnimation();

    }
 });

}



