<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RealFaviconGenerator API demo project</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="container">
      <h1>RealFaviconGenerator API demo</h1>

      <p>
      This is a sample project. Below, you can choose various settings, 
      like the pre-defined location of the favicon files on the target web site, etc.
      Usually, most of these settings are not presented to the end-user but 
      chosen in the background by the CMS or plugin.
      </p>
      
      <h2>Master Picture</h2>
      
      <div class="radio">
        <label>
          <input type="radio" name="master_picture" id="master_picture_url" value="master_picture_url" checked>
            Master picture is predefined and passed by URL 
            (<code><span id="demo_picture_url">http://realfavicongenerator.net/demo_favicon.png</span></code> in this example).
        </label>
      </div>
      <div class="radio">
        <label>
          <input type="radio" name="master_picture" id="master_picture_inline" value="master_picture_inline">
            Master picture is predefined and passed directly, encoded in Base64.
        </label>
      </div>
      <div class="radio">
        <label>
          <input type="radio" name="master_picture" id="master_picture_none" value="master_picture_none">
            No master picture. The user will choose one from RealFaviconGenerator.
        </label>
      </div>
      
      <div class="checkbox" id="demo_picture_container" style="opacity: 0">
        <label>
          <input name="demo_picture" id="demo_picture" type="checkbox" value="demo_picture">
            The user can pick a demo picture.
        </label>
      </div>
      
      <h2>Files location</h2>
      
      <div class="radio">
        <label>
          <input type="radio" name="files_location" id="files_location_root" value="files_location_root" checked>
            Favicon files will be in the root directory of the target web site.
        </label>
      </div>
      <div class="radio">
        <label>
          <input type="radio" name="files_location" id="files_location_not_root" value="files_location_not_root">
            Favicon files will be in another directory.
        </label>
      </div>
      <div class="form-group" id="files_path_container" style="opacity: 0">
        <input type="text" class="form-control" id="files_location_path" placeholder="/path/to/icons or http://somesite.com/path/to/icons">
      </div>
      
      <h2>API Key</h2>
      
      <p>
      No need to change this default value :)
      </p>
      <div class="form-group">
        <input type="text" class="form-control" id="api_key" value="87d5cd739b05c00416c4a19cd14a8bb5632ea563">
      </div>
      
      <h2>Callback</h2>

      <div class="radio">
        <label>
          <input type="radio" name="callback" id="callback_none" value="callback_none">
            No callback, the user downloads the favicon files directly from RealFaviconGenerator.
        </label>
      </div>
      <div class="radio">
        <label>
          <input type="radio" name="callback" id="callback_url" value="callback_url" checked>
            After the favicon creation, the user is redirected to the caller.
        </label>
      </div>
      <div class="form-group" id="custom_parameter_container">
      	<p>
          Custom parameter
          <input type="text" class="form-control" id="custom_parameter" placeholder="someparam1234">
        </p>
      </div>
      
      <form role="form" method="post" action="http://realfavicongenerator.net/api/favicon_generator" id="favicon_form">
        <div class="form-group">
          <input type="hidden" name="json_params" id="json_params"/>
          <button type="submit" id="form_button" disabled="disabled" class="btn btn-primary">Go to RealFaviconGenerator and create the favicon</button>
        </div>
      </form>
    </div>
  </body>
  <script type="text/javascript" src="/jquery-1.11.0.min.js"></script>
  <script type="text/javascript">
    var picData = null;
    
    // See http://stackoverflow.com/questions/934012/get-image-data-in-javascript
    // Credits: Matthew Crumley
    function getBase64Image(img) {
      // Create an empty canvas element
      var canvas = document.createElement("canvas");
      canvas.width = img.width;
      canvas.height = img.height;
        
      // Copy the image contents to the canvas
      var ctx = canvas.getContext("2d");
      ctx.drawImage(img, 0, 0);
    
      // Get the data-URL formatted image
      // Firefox supports PNG and JPEG. You could check img.src to
      // guess the original format, but be aware the using "image/jpg"
      // will re-encode the image.
      var dataURL = canvas.toDataURL("image/png");
        
      return dataURL.replace(/^data:image\/(png|jpg);base64,/, "");
    }
    
    function computeJson() {
      var params = { favicon_generation: { 
        callback: {},
        master_picture: {},
        files_location: {},
        api_key: $('#api_key').val()
      }};
      
      switch($('input[name=master_picture]:checked').val()) {
        case('master_picture_none'):
          params.favicon_generation.master_picture.type = "no_picture";
          params.favicon_generation.master_picture.demo = $('#demo_picture').is(':checked');
          break;
        case('master_picture_url'):
          params.favicon_generation.master_picture.type = "url";
          params.favicon_generation.master_picture.url = $('#demo_picture_url').html();
          break;
        case('master_picture_inline'):
          params.favicon_generation.master_picture.type = "inline";
          params.favicon_generation.master_picture.content = picData;
          break;
      }
      
      switch($('input[name=files_location]:checked').val()) {
        case('files_location_root'):
          params.favicon_generation.files_location.type = 'root';
          break;
        case('files_location_not_root'):
          params.favicon_generation.files_location.type = 'path';
          params.favicon_generation.files_location.path = $('#files_location_path').val();
          break;
      }

      switch($('input[name=callback]:checked').val()) {
        case('callback_none'):
          params.favicon_generation.callback.type = 'none';
          break;
        case('callback_url'):
          params.favicon_generation.callback.type = 'url';
          params.favicon_generation.callback.url = "http://" + window.location.hostname + "/back";
          if ($('#custom_parameter').val().length > 0) {
            params.favicon_generation.callback.custom_parameter = $('#custom_parameter').val();
          }
          break;
      }
      
      return params;
    }
    
    $(document).ready(function() {
      var img = new Image;
      img.src = '/demo_favicon.png';
      img.onload = function() {
        picData = getBase64Image(img);
        
        $('#form_button').removeAttr('disabled');
      }
      
      $('#favicon_form').submit(function(e) {
        $('#json_params').val(JSON.stringify(computeJson()));
      });
      
      $('[name=master_picture]').change(function() {
        if ($('input[name=master_picture]:checked').val() == 'master_picture_none') {
          $('#demo_picture_container').animate({ opacity: 1 });
        }
        else {
          $('#demo_picture_container').animate({ opacity: 0 });
        }
      });
      
      $('[name=files_location]').change(function() {
        if ($('input[name=files_location]:checked').val() == 'files_location_not_root') {
          $('#files_path_container').animate({ opacity: 1 });
        }
        else {
          $('#files_path_container').animate({ opacity: 0 });
        }
      });
      
      $('[name=callback]').change(function() {
        if ($('input[name=callback]:checked').val() == 'callback_url') {
          $('#custom_parameter_container').animate({ opacity: 1 });
        }
        else {
          $('#custom_parameter_container').animate({ opacity: 0 });
        }
      });
    });
  </script>
</html>
