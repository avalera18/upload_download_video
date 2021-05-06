<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Catalog</title>
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/template-video-catalog.css">
	<script type="text/javascript" src="freesound.js"></script>
</head>
<body>
<script type="text/javascript">

    window.onload = function(){

        freesound.setToken("ZwOcutC9TMM3Z7nLIKmKoz0OALWhFO2ECIPgyLVL");
        
        var fields = 'id,name,url,analysis,username';
        // Example 1
        // Example of geeting the info of a sound, queying for similar sounds (content based) and showing some analysis
        // features. Both similar sounds and analysis features are obtained with additional requests to the api.
        freesound.getSound(96525,
                function(sound){
                    var msg = "";

                    msg = "<h3>Getting info of sound: " + sound.name + "</h3>";
                    msg += "<strong>Url:</strong> " + sound.url + "<br>";
                    msg += "<strong>Description:</strong> " + sound.description + "<br>";
                    msg += "<strong>Tags:</strong><ul>";
                    for (i in sound.tags){
                        msg += "<li>" + sound.tags[i] + "</li>";
                    }
                    msg += "</ul><br>";
                    msg += "<img src='" + sound.images.waveform_l + "'>";

                    snd = new Audio(sound.previews['preview-hq-mp3']);
                    msg += '<br><button onclick="snd.play()">play</button><button onclick="snd.pause()">pause</button><br><br>';
                    displayMessage(msg,'resp1');                    

                    // When we have printed some sound info, ask for analysis
                    sound.getAnalysis(null,function(analysis){
                        msg += "<strong>Mfccs:</strong><ul>";
                        for (i in analysis.lowlevel.mfcc.mean){
                            msg += "<li>" + analysis.lowlevel.mfcc.mean[i] + "</li>"
                        }
                        msg += "</ul>";
                        displayMessage(msg,'resp1')

                        // When we have printed the analysis, ask for similar sounds
                        sound.getSimilar(function(sounds){
                            msg += "<strong>Similar sounds:</strong><ul>";
                            
                            for (i =0;i<=10;i++){                                
                                var snd = sounds.getSound(i);
                                msg += "<li>" + snd.id + ": " + snd.url + "</li>"
                            }
                            msg += "</ul>";

                            displayMessage(msg,'resp1')
                        }, function(){ displayError("Similar sounds could not be retrieved.")},
                        {fields:fields});
                    }, function(){ displayError("Analysis could not be retrieved.")},
                    true);// showAll
                }, function(){ displayError("Sound could not be retrieved.")}
        );
        
        
        // Example 2
        // Example of searching sounds: querying the freesound db for sounds and retrieving lowlevel descriptors
        var query = "violoncello"
        var page = 1
        var filter = "tag:tenuto duration:[1.0 TO 15.0]"
        var sort = "rating_desc"
        var descriptors = "lowlevel.spectral_centroid"
        freesound.textSearch(query, {page:page, filter:filter, sort:sort, fields:fields, descriptors: descriptors},
            function(sounds){
                var msg = ""
                
                msg = "<h3>Searching for: " + query + "</h3>"
                msg += "With filter: " + filter +" and sorting: " + sort + "<br>"
                msg += "Num results: " + sounds.count + "<br><ul>"
                for (i =0;i<=10;i++){  
                    var snd = sounds.getSound(i);
                    msg += "<li>" + snd.name + " by " + snd.username + " with spectral centroid mean value: " + snd.analysis.lowlevel.spectral_centroid.mean.toString() + "</li>"
                }
                msg += "</ul>"
                displayMessage(msg,"resp2")
            },function(){ displayError("Error while searching...")}
        );
        
        // Example 3
        // Example of content based searching
        var t = '.lowlevel.pitch_salience.mean:1.0 .lowlevel.pitch.mean:440';
        var f = ".lowlevel.pitch.var:[* TO 20] AND .metadata.audio_properties.length:[1 TO 10]";
        var page_size  = 10;
        
        freesound.contentSearch({target:t,filter:f, page_size : page_size, fields:fields},
            function(sounds){                
                var msg = ""
                msg = "<h3>Content based searching</h3>"
                msg += "Target: " + t +"<br>"
                msg += "Filter: " + f +"<br>"
                msg += "Fields: " + fields +"<br>"
                msg += "Num results: " + sounds.count + "<br><ul>"
                msg += "<li> ---------- PAGE 1 ---------- </li>"
                for (i in sounds.results){                                        
                    msg += "<li>" +  sounds.results[i].id+ " | " +
                        sounds.results[i].name + " | " + sounds.results[i].url + "</li>"
                }
                msg += "</ul>"
                displayMessage(msg,"resp3")

                // Once we got the first page of results, go to the following one
                sounds.nextPage(
                        function(sounds){
                            msg += "<ul><li> ---------- PAGE 2 ---------- </li>"
                            for (i in sounds.results){
                                var j = parseInt(i);
                                msg += "<li>" +  sounds.results[j].id.toString(10) + 
                                    " | " + sounds.results[j].name + " | " + sounds.results[j].url + "</li>"
                            }
                            msg += "</ul>"
                            displayMessage(msg,"resp3")
                        },
                        function(){ displayError("Error getting next page...")})
            },function(){ displayError("Error while content based searching...")}
        );

        
        // Example 4
        // Example of geoquerying
        var min_lat = 41.3265528618605;
        var max_lat = 41.4504467428547;
        var min_lon = 2.005176544189453;
        var max_lon = 2.334766387939453;
        filterString = "geotag:\"Intersects("+min_lon.toFixed(3)+" "+min_lat.toFixed(3)+" "+max_lon.toFixed(3)+" "+max_lat.toFixed(3)+")\"";
        freesound.textSearch("",{filter:filterString, fields:fields},
                function(sounds){
                    var msg = ""
                    msg = "<h3>Geoquerying</h3>"
                    msg += "Min lat: " + min_lat +"<br>"
                    msg += "Max lat: " + max_lat +"<br>"
                    msg += "Min lon: " + min_lon +"<br>"
                    msg += "Max lon: " + max_lon +"<br>"
                    msg += "Num results: " + sounds.count + "<br><ul>"
                    for (i in sounds.results){
                        msg += "<li>" +  sounds.results[i].id + " | " + 
                            sounds.results[i].name + " | " + 
                            sounds.results[i].url + "</li>";
                    }
                    msg += "</ul>"
                    displayMessage(msg,"resp4")
                },function(err){ console.log(err);displayError("Error while geoquerying...")}
        );

        
        freesound.getUser("Jovica",
            function(user){
                var msg = "";
                msg = "<h3>User info</h3>";
                msg += "Username: " + user.username +"<br>";
                // Get user sounds
                user.sounds(
                        function(sounds){
                            msg += "User sounds:<ul>"
                            for (i in sounds.results){
                                msg += "<li>" +  sounds.results[i].id + " | " + 
                                    sounds.results[i].name + " | " + 
                                    sounds.results[i].url + "</li>";
                            }
                            msg += "</ul>"
                            displayMessage(msg,"resp5")
                        },null,{fields:fields}
                )
            }, function(){ displayError("Error getting user info...")}
        );
    };

    function displayError(text){
        document.getElementById('error').innerHTML=text;
    }

    function displayMessage(text,place){
        document.getElementById(place).innerHTML=text;
    }


</script>

	<div class="tm-page-wrap mx-auto">
			<div class="position-relative">
			<div class="potition-absolute tm-site-header">
				<div class="container-fluid position-relative">
					<div class="row">						
                        <div class="col-7 col-md-4">
                            <a href="index.html" class="tm-bg-black text-center tm-logo-container">
                                <i class="fas fa-video tm-site-logo mb-3"></i>
                                <h1 class="tm-site-name">Home Page</h1>
                            </a>
                        </div>
                        <div class="col-5 col-md-8 ml-auto mr-0">
                            <div class="tm-site-nav">
                                <nav class="navbar navbar-expand-lg mr-0 ml-auto" id="tm-main-nav">
                                    <button class="navbar-toggler tm-bg-black py-2 px-3 mr-0 ml-auto collapsed" type="button"
                                        data-toggle="collapse" data-target="#navbar-nav" aria-controls="navbar-nav"
                                        aria-expanded="false" aria-label="Toggle navigation">
                                        <span>
                                            <i class="fas fa-bars tm-menu-closed-icon"></i>
                                            <i class="fas fa-times tm-menu-opened-icon"></i>
                                        </span>
                                    </button>
                                    <div class="collapse navbar-collapse tm-nav" id="navbar-nav">
                                        <ul class="navbar-nav text-uppercase">
                                            <li class="nav-item active">
                                                <a class="nav-link tm-nav-link" href="index.html">Video Page <span class="sr-only">(current)</span></a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link tm-nav-link" href="contact.html">Contact</a>
                                            </li>
                                        </ul>
                                    </div>
                                </nav>
                            </div>
                        </div>
					</div>
				</div>
			</div>
			<div class="tm-welcome-container text-center text-white">
                <div class="tm-welcome-container-inner">
                    <p class="tm-welcome-text mb-1 text-white">Take a look the following site </p>
                    <p class="tm-welcome-text mb-5 text-white">for more experience on analyzer music and video.</p>
                    <a href="https://music-cellar.accusonus.com/" class="btn tm-btn-animate tm-icon-down">
                        <span>Discover</span>
                    </a>
                </div>
            </div>

			<!-- Header image -->
            <div id="tm-fixed-header-3"></div>  
		</div>
		<div class="container-fluid">
			<div class="mx-auto tm-content-container">
				<main>
					<div class="row mb-5 pb-4">
						<div class="col-12">
							<?php

							// Getting uploaded file
							$file = $_FILES["file"];

								if (file_exists($file["name"])){

									echo "The file " . $file["name"] . " does not exist";
								}else{
									// Uploading in "uplaod" folder
									move_uploaded_file($file["tmp_name"], "upload/" . $file["name"]);
									echo "<video width='400' height='400' controls> <source src='get_sampleVideo/" . $file["name"] . "' type='video/mp4'> </video>";
									echo "<br>";
									echo "<audio  controls> <source src='get_sampleMusic/" . $_FILES["file"]["name"] . "' type='audio/mp3'> </audio>";
									echo "<br>";
									echo "Freesound API";
									echo "<div id='resp1'></div>";
								}

							?>
						</div>
					</div>	
					<div class="row mb-5 pb-5">
						<div class="col-xl-4 col-lg-5">
							<p>List of sample videos : </p>
							<?php
								$files = scandir("upload");
								for ($a = 2; $a < count($files); $a++)
								{
									?>
									<div class="tm-bg-gray tm-share-box">
										<p>
											<?php echo $files[$a]; ?>

											<a href="upload/<?php echo $files[$a]; ?>" download="<?php echo $files[$a]; ?>">
												Download
											</a>
											
											<a href="delete.php?name=upload/<?php echo $files[$a]; ?>" style="color: red;">
												Delete
											</a>
										</p>
									</div>
									<?php
								}
							?>
						</div>
					</div>
				</main>
			</div>
		</div>
	</div>
	<script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/parallax.min.js"></script>
</body>
</html>