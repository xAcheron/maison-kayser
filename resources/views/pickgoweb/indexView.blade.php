<!DOCTYPE html>
<html lang="en" class="js-focus-visible" data-js-focus-visible="">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="">
		<meta name="author" content="">
		<link rel="icon" type="image/png" sizes="96x96" href="https://www.maison-kayser.com.mx/img/favicon-1.ico?1523004325">
		<title>Maison Kayser México</title>

		<!-- Bootstrap core CSS -->
		<link href="{{ asset('BS4/css/bootstrap.min.css') }}" rel="stylesheet">
		<link href="{{ asset('BS4/css/open-iconic-bootstrap.min.css') }}" rel="stylesheet">
		<!-- Custom styles for this template -->
		<link href="{{ asset('BS4/css/navbar-top-fixed.css') }}" rel="stylesheet">
		<style type="text/css">
			@font-face {
			  font-weight: 400;
			  font-style:  normal;
			  font-family: 'Circular-Loom';

			  src: url('https://cdn.loom.com/assets/fonts/circular/CircularXXWeb-Book-cd7d2bcec649b1243839a15d5eb8f0a3.woff2') format('woff2');
			}

			@font-face {
			  font-weight: 500;
			  font-style:  normal;
			  font-family: 'Circular-Loom';

			  src: url('https://cdn.loom.com/assets/fonts/circular/CircularXXWeb-Medium-d74eac43c78bd5852478998ce63dceb3.woff2') format('woff2');
			}

			@font-face {
			  font-weight: 700;
			  font-style:  normal;
			  font-family: 'Circular-Loom';

			  src: url('https://cdn.loom.com/assets/fonts/circular/CircularXXWeb-Bold-83b8ceaf77f49c7cffa44107561909e4.woff2') format('woff2');
			}

			@font-face {
			  font-weight: 900;
			  font-style:  normal;
			  font-family: 'Circular-Loom';

			  src: url('https://cdn.loom.com/assets/fonts/circular/CircularXXWeb-Black-bf067ecb8aa777ceb6df7d72226febca.woff2') format('woff2');
			}
			.navbar-brand {
				height: 60px;
			}
			.navbar-brand img {
				max-height: 100%;
				display: inline-block;
				padding: 5px 0;
			}
			.shopHeader {
				margin-bottom: 1rem;
				position: relative;
			}
			.hdContainer {
				height: 16rem;
				background-color: #777;
				padding: 10px 30px;
				background-image: url("{{ asset('BS4/img/3-Maison_Kayser_Reforma.jpg') }}");
			}
            #map {
                height: 100%;
            }
			nav .navbar-nav li a{
			  color: black !important;
			}

			.min-border {
				height: 160px;
				border: 1px solid #D5D6D7;
				overflow: hidden;
				align-items: stretch;
				border-radius: 8px;
				padding: 0px;
				position: relative;
				transition: all;
				transition-duration: .25s;
				transition-timing-function: ease;
				cursor: pointer;
				margin-bottom: 5px;
			}
			.description-col{
				padding: 5px;
			}

			.store-box {
				float: left; margin-left: 10px;
				background-color: rgba(255, 255, 255, 0.8); 
				width: 450px;padding: 18px;height: 15rem;
				border-radius: 8px;
			}

            .loginLink {
                color: #000;
                font-weight: bold;
                margin-right: 10px;
                border: 1px solid transparent;
                text-align: center;
                padding: 6px 6px;
                border-radius: .25rem;
                text-decoration: none;
                display: inline-block;
            }
            .loginLink:hover {
/*                color: #ffc107;
                background-color: transparent;
                background-image: none;
                border-color: #ffc107;
                
                background-color: white;
                font-size: 16px;
*/
                color: black;
                border: 1px solid #ffc107;
                text-align: center;
                padding: 6px 6px;
                border-radius: .25rem;
                text-decoration: none;
                display: inline-block;
            }
		</style>
	</head>
	<body cz-shortcut-listen="true">
    <nav class="navbar navbar-expand-md navbar-light fixed-top bg-light">
      <div class="navbar-brand"><img src="{{ asset('BS4/img/logo.png') }}"></div>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav mr-auto"><!--
          <li class="nav-item active">
            <a class="nav-link" href="https://getbootstrap.com/docs/4.1/examples/navbar-fixed/#">Home <span class="sr-only">(current)</span></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="https://getbootstrap.com/docs/4.1/examples/navbar-fixed/#">Link</a>
          </li>
          <li class="nav-item">
            <a class="nav-link disabled" href="https://getbootstrap.com/docs/4.1/examples/navbar-fixed/#">Disabled</a>
          </li>-->
        </ul>
        <form class="form-inline mt-2 mt-md-0">
          <a href="#" class="loginLink" >Iniciar Sesión</a>
          <button class="btn btn-outline-warning my-2 my-sm-0" type="submit"><span class="oi oi-basket"></span> Cesta</button>
        </form>
      </div>
    </nav>

	<main role="main" style="height: 600px;">
        <div class="row w-100 h-100 p-0 m-0">
            <div class="col-6 m-0 p-0">
                <div class="hdContainer">
                    <div class="store-box">
                        <div class="row">
                          <div class="col-12">
                            <h2>Maison Kayser México</h2>
                            <p class="lead">Encuentra tu Maison Kayser mas cercano</p>
                          </div>
                        </div>
                        <div class="row" style="margin-bottom: 10px;">
                          <div class="col-12">
                            <a class="btn btn-sm btn-warning w-100" href="#" role="button" onclick="getLocation()"><span class="oi oi-map-marker"></span> Obtener Ubicación</a>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-6">
                            <input type="text" class="w-100" id="zipcode">
                          </div>
                          <div class="col-6">
                            <a class="btn btn-sm btn-warning w-100" href="#" role="button" onclick="codeAddress()"><span class="oi oi-find"></span>Buscar Codigo Postal</a>
                          </div>
                        </div>
                    </div>
                </div>
               <div class="w-100 h-100 p-4">
                <div id="loading-bar" class="w-100 d-none">
                  <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
                  </div>
                  Buscando...
                </div>
                <div id="near_locations" class="w-100 h-100"></div>
               </div>
            </div>
            <div class="col-6 m-0 p-0">
                <div id="map"></div>
            </div>
        </div>
    </main>
		

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="{{ asset('BS4/js/jquery-3.3.1.slim.min.js' ) }}" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
    <script src="{{ asset('BS4/js/popper.min.js') }}"></script>
    <script src="{{ asset('BS4/js/bootstrap.min.js') }}"></script>
    <script
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDAx4inmlG6KTfxNn-xnAwuyThGTjWRI3s&callback=initMap&libraries=&v=weekly"
      async
    ></script> 
    <script>
        
        var map;
        var geocoder;

        function initMap() {

          geocoder = new google.maps.Geocoder();

          map = new google.maps.Map(document.getElementById("map"), {
              center: { lat: 19.3586669, lng: -99.1602941 },
              zoom: 8,
              streetViewControl: false,
              mapTypeId: "roadmap",
              mapTypeControlOptions: {
                mapTypeIds: ["roadmap"],
              }
          });

          map.addListener("mouseup", () => { 
            const location = [map.getCenter().lat(), map.getCenter().lng()];
            loadLocations(location);
          });

        }


      function getLocation() {
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(setPosition);
        } else { 
          x.innerHTML = "Geolocation is not supported by this browser.";
        }
      }

      function setPosition(position) {
        const pos = {
            lat: position.coords.latitude,
            lng: position.coords.longitude,
        };
        map.setCenter(pos);
        map.setZoom(14);
        const location = [position.coords.latitude,position.coords.longitude];
        loadLocations(location);
      }

      function codeAddress() {
        var address = document.getElementById('zipcode').value;
        geocoder.geocode( { 'address': address}, function(results, status) {

          if (status == 'OK') {
            map.setCenter(results[0].geometry.location);
            map.setZoom(14);
/*            var marker = new google.maps.Marker({
                map: map,
                position: results[0].geometry.location
            }); */
          } else {
          
            alert('Geocode was not successful for the following reason: ' + status);

          }

        });

      }

      function loadLocations(location) {
        
        var loading_bar = document.getElementById("loading-bar");
        loading_bar.classList.remove("d-none");
        loading_bar.classList.add("d-block");
        
        var xhttp = new XMLHttpRequest();
        
        xhttp.onreadystatechange = function() {

          if (this.readyState == 4 && this.status == 200) {

            var results = JSON.parse(this.responseText);
            
            document.getElementById("near_locations").innerHTML = "";
            
            let htmlString ="";
            var marker;
            for (store of results.saurcersRecommends) {
              htmlString += '<div class="row" onclick="setStore('+store.idBranch+')"><div class="col-12"><div class="min-border"><div class="d-flex flex-row flex-nowrap"><div><div class="description-col"><h6>'+store.nameBranch+'</h6><p>'+store.direction+'</p><span class="badge badge-secondary">'+store.KmDistanceAprox+' KM</span></div></div><div class="ml-auto"><img style="height: 160px;" src="https://intranet.prigo.com.mx/storage/platillos/imagenes/74/full.png"></div></div></div></div></div>';
              console.log({ lat: Number(store.lat), lng: Number(store.lng) });
              marker = new google.maps.Marker({
                map: map,
                position: { lat: Number(store.lat), lng: Number(store.lng) }
              }); 
            }

            document.getElementById("near_locations").innerHTML = htmlString;
            loading_bar.classList.remove("d-block");
            loading_bar.classList.add("d-none");
            
          }

        };

        xhttp.open("GET", "{{ route('getLocations') }}?lat="+ location[0]+"&lng="+location[1], true);

        xhttp.send();

      }

      function setStore(store)
      {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
              var results = JSON.parse(this.responseText);
              if(results.success)
                document.location.href = "{{ route('menuPickGo') }}";

          }
          else
          {

          }
        };

        xhttp.open("GET", "{{ route('saveSucFavoritaWeb') }}?idSuc="+ store, true);

        xhttp.send();

        
      }

      getLocation();
</script>
</body></html>