<!DOCTYPE html>
<?php
   define('BASE_DIR', dirname(__FILE__));
   require_once(BASE_DIR.'/config.php');
   $config = array();
   $debugString = "";
   $macros = array('error_soft','error_hard','start_img','end_img','start_vid','end_vid','end_box','do_cmd','motion_event','startstop');
   $options_mm = array('Average' => 'average', 'Spot' => 'spot', 'Backlit' => 'backlit', 'Matrix' => 'matrix');
   $options_em = array('Off' => 'off', 'Auto' => 'auto', 'Night' => 'night', 'Nightpreview' => 'nightpreview', 'Backlight' => 'backlight', 'Spotlight' => 'spotlight', 'Sports' => 'sports', 'Snow' => 'snow', 'Beach' => 'beach', 'Verylong' => 'verylong', 'Fixedfps' => 'fixedfps');
   $options_wb = array('Off' => 'off', 'Auto' => 'auto', 'Sun' => 'sun', 'Cloudy' => 'cloudy', 'Shade' => 'shade', 'Tungsten' => 'tungsten', 'Fluorescent' => 'fluorescent', 'Incandescent' => 'incandescent', 'Flash' => 'flash', 'Horizon' => 'horizon');
//   $options_ie = array('None' => 'none', 'Negative' => 'negative', 'Solarise' => 'solarise', 'Sketch' => 'sketch', 'Denoise' => 'denoise', 'Emboss' => 'emboss', 'Oilpaint' => 'oilpaint', 'Hatch' => 'hatch', 'Gpen' => 'gpen', 'Pastel' => 'pastel', 'Watercolour' => 'watercolour', 'Film' => 'film', 'Blur' => 'blur', 'Saturation' => 'saturation', 'Colourswap' => 'colourswap', 'Washedout' => 'washedout', 'Posterise' => 'posterise', 'Colourpoint' => 'colourpoint', 'ColourBalance' => 'colourbalance', 'Cartoon' => 'cartoon');
// Remove Colourpoint and colourbalance as they kill the camera
   $options_ie = array('None' => 'none', 'Negative' => 'negative', 'Solarise' => 'solarise', 'Sketch' => 'sketch', 'Denoise' => 'denoise', 'Emboss' => 'emboss', 'Oilpaint' => 'oilpaint', 'Hatch' => 'hatch', 'Gpen' => 'gpen', 'Pastel' => 'pastel', 'Watercolour' => 'watercolour', 'Film' => 'film', 'Blur' => 'blur', 'Saturation' => 'saturation', 'Colourswap' => 'colourswap', 'Washedout' => 'washedout', 'Posterise' => 'posterise', 'Cartoon' => 'cartoon');
   $options_ce_en = array('Disabled' => '0', 'Enabled' => '1');
   $options_ro = array('No rotate' => '0', 'Rotate_90' => '90', 'Rotate_180' => '180', 'Rotate_270' => '270');
   $options_fl = array('None' => '0', 'Horizontal' => '1', 'Vertical' => '2', 'Both' => '3');
   $options_bo = array('Off' => '0', 'Background' => '2');
   $options_av = array('V2' => '2', 'V3' => '3');
   $options_at_en = array('Disabled' => '0', 'Enabled' => '1');
   $options_ac_en = array('Disabled' => '0', 'Enabled' => '1');
   $options_ab = array('Off' => '0', 'On' => '1');
   $options_vs = array('Off' => '0', 'On' => '1');
   $options_rl = array('Off' => '0', 'On' => '1');
   $options_vp = array('Off' => '0', 'On' => '1');
   $options_mx = array('Internal' => '0', 'External' => '1', 'Monitor' => '2');
   $options_mf = array('Off' => '0', 'On' => '1');
   $options_cn = array('First' => '1', 'Second' => '2');
   $options_st = array('Off' => '0', 'On' => '1');
   
   function initCamPos() {
      $tr = fopen("pipan_bak.txt", "r");
      if($tr){
         while(($line = fgets($tr)) != false) {
           $vals = explode(" ", $line);
           echo '<script type="text/javascript">init_pt(',$vals[0],',',$vals[1],');</script>';
         }
         fclose($tr);
      }
   }

   function user_buttons() {
      $buttonString = "";
	  $buttonCount = 0;
      if (file_exists("userbuttons")) {
		$lines = array();
		$data = file_get_contents("userbuttons");
		$lines = explode("\n", $data);
		foreach($lines as $line) {
			if (strlen($line) && (substr($line, 0, 1) != '#') && buttonCount < 6) {
				$index = explode(",",$line);
				if ($index !== false) {
					$buttonName = $index[0];
					$macroName = $index[1];
					$className = $index[2];
					if ($className == false) {
						$className = "btn btn-primary";
					}
					$otherAtt  = $index[3];
					$buttonString .= '<input id="' . $buttonName . '" type="button" value="' . $buttonName . '" onclick="send_cmd(' . "'sy " . $macroName . "'" . ')" class="' . $className . '" ' . $otherAtt . '>' . "\r\n";
					$buttonCount += 1;
				}
			}
		}
      }
	  if (strlen($buttonString)) {
		  echo '<div class="container-fluid text-center">' . $buttonString . "</div>\r\n";
	  }
   }

   function pan_controls() {
      $mode = 0;
      if (file_exists("pipan_on")){
         initCamPos();
         $mode = 1;
      } else if (file_exists("servo_on")){
         $mode = 2;
      }
      if ($mode <> 0) {
         echo '<script type="text/javascript">set_panmode(',$mode,');</script>';
         echo "<div class='container-fluid text-center liveimage'>";
         echo "<div alt='Up' id='arrowUp' style='margin-bottom: 2px;width: 0;height: 0;border-left: 20px solid transparent;border-right: 20px solid transparent;border-bottom: 40px solid #428bca;font-size: 0;line-height: 0;vertical-align: middle;margin-left: auto; margin-right: auto;' onclick='servo_up();'></div>";
         echo "<div>";
         echo "<div alt='Left' id='arrowLeft' style='margin-right: 22px;display: inline-block;height: 0;border-top: 20px solid transparent;border-bottom: 20px solid transparent;border-right: 40px solid #428bca;font-size: 0;line-height: 0;vertical-align: middle;' onclick='servo_left();'></div>";
         echo "<div alt='Right' id='arrowRight' style='margin-left: 22px;display: inline-block;height: 0;border-top: 20px solid transparent;border-bottom: 20px solid transparent;border-left: 40px solid #428bca;font-size: 0;line-height: 0;vertical-align: middle;' onclick='servo_right();'></div>";
         echo "</div>";
         echo "<div alt='Down' id='arrowDown' style='margin-top: 2px;width: 0;height: 0;border-left: 20px solid transparent;border-right: 20px solid transparent;border-top: 40px solid #428bca;font-size: 0;line-height: 0;vertical-align: middle;margin-left: auto; margin-right: auto;' onclick='servo_down();'></div>";
         echo "</div>";
      }
   }
  
   function pilight_controls() {
      echo "<tr>";
        echo "<td>Pi-Light:</td>";
        echo "<td>";
          echo "R: <input type='text' size=4 id='pilight_r' value='255'>";
          echo "G: <input type='text' size=4 id='pilight_g' value='255'>";
          echo "B: <input type='text' size=4 id='pilight_b' value='255'><br>";
          echo "<input type='button' value='ON/OFF' onclick='led_switch();'>";
        echo "</td>";
      echo "</tr>";
   }

   function getExtraStyles() {
      $files = scandir('css');
      foreach($files as $file) {
         if(substr($file,0,3) == 'es_') {
            echo "<option value='$file'>" . substr($file,3, -4) . '</option>';
         }
      }
   }
   
  
   function makeOptions($options, $selKey) {
      global $config;
      switch ($selKey) {
         case 'flip': 
            $cvalue = (($config['vflip'] == 'true') || ($config['vflip'] == 1) ? 2:0);
            $cvalue += (($config['hflip'] == 'true') || ($config['hflip'] == 1) ? 1:0);
            break;
         case 'MP4Box': 
            $cvalue = $config[$selKey];
            if ($cvalue == 'background') $cvalue = 2;
            break;
         default: $cvalue = $config[$selKey]; break;
      }
      if ($cvalue == 'false') $cvalue = 0;
      else if ($cvalue == 'true') $cvalue = 1;
      foreach($options as $name => $value) {
         if ($cvalue != $value) {
            $selected = '';
         } else {
            $selected = ' selected';
         }
         echo "<option value='$value'$selected>$name</option>";
      }
   }

   function makeInput($id, $size, $selKey='', $type='text') {
      global $config, $debugString;
      if ($selKey == '') $selKey = $id;
      switch ($selKey) {
         case 'tl_interval': 
            if (array_key_exists($selKey, $config)) {
               $value = $config[$selKey] / 10;
            } else {
               $value = 3;
            }
            break;
         case 'watchdog_interval':
            if (array_key_exists($selKey, $config)) {
               $value = $config[$selKey] / 10;
            } else {
               $value = 0;
            }
            break;
         default: $value = $config[$selKey]; break;
      }
      echo "<input type='{$type}' size=$size id='$id' value='$value' style='width:{$size}em;'>";
   }
   
   function macroUpdates() {
      global $config, $debugString, $macros;
	  $m = 0;
	  $mTable = '';
	  foreach($macros as $macro) {
		  $value = $config[$macro];
		  if(substr($value,0,1) == '-') {
			  $checked = '';
			  $value = substr($value,1);
		  } else {
			  $checked = 'checked';
		  }
		  $mTable .= "<TR><TD>Macro:$macro</TD><TD><input type='text' size=16 id='$macro' value='$value'>\r\n";
		  $mTable .= "<input type='checkbox' $checked id='$macro" . "_chk'>\r\n";
		  $mTable .= "<input type='button' value='OK' onclick=" . '"send_macroUpdate' . "($m,'$macro')\r\n" . ';"></TD></TR>';
		  $m++;
	  }
      echo $mTable;
   }

   function getImgWidth() {
      global $config;
      if($config['vector_preview'])
         return 'style="width:' . $config['width'] . 'px;"';
      else
         return '';
   }
   
   function getLoadClass() {
      global $config;
      if(array_key_exists('fullscreen', $config) && $config['fullscreen'] == 1)
         return 'class="fullscreen" ';
      else
         return '';
   }

   function simple_button() {
	   global $toggleButton, $userLevel;
	   if ($toggleButton != "Off" && $userLevel > USERLEVEL_MIN) {
		  echo '<input id="toggle_display" type="button" class="btn btn-primary" value="' . $toggleButton . '" style="position:absolute;top:60px;right:10px;" onclick="set_display(this.value);">';
	   }
   }

   if (isset($_POST['extrastyle'])) {
      if (file_exists('css/' . $_POST['extrastyle'])) {
         $fp = fopen(BASE_DIR . '/css/extrastyle.txt', "w");
         fwrite($fp, $_POST['extrastyle']);
         fclose($fp);
      }
   }

   function getDisplayStyle($context, $userLevel) {
	    global $Simple;
	    if ($Simple == 1) {
			echo 'style="display:none;"';
		} else {
			switch($context) {
				case 'navbar':
					if ((int)$userLevel < (int)USERLEVEL_MEDIUM)
						echo 'style="display:none;"';
					break;
				case 'preview':
					if ((int)$userLevel < (int)USERLEVEL_MINP)
						echo 'style="display:none;"';
					break;
				case 'actions':
					if ((int)$userLevel < (int)USERLEVEL_MEDIUM)
						echo 'style="display:none;"';
					break;
				case 'settings':
					if ((int)$userLevel != (int)USERLEVEL_MAX)
						echo 'style="display:none;"';
					break;
			}
		}
   }

   $toggleButton = "Off";
   $Simple = 0;
   $allowSimple = "SimpleOn";
   if(isset($_COOKIE["display_mode"])) {
      if($_COOKIE["display_mode"] == "Full") {
		 $allowSimple = "SimpleOff";
         $toggleButton = "Simple";
         $Simple = 2;
      } else if($_COOKIE["display_mode"] == "Simple") {
		 $allowSimple = "SimpleOff";
         $toggleButton = "Full";
         $Simple = 1;
      } else {
		 $allowSimple = "SimpleOn";
         $toggleButton = "Off";
         $Simple = 0;
	  }
   }
  
   $streamButton = "MJPEG-Stream";
   $mjpegmode = 0;
   if(isset($_COOKIE["stream_mode"])) {
      if($_COOKIE["stream_mode"] == "MJPEG-Stream") {
         $streamButton = "Default-Stream";
         $mjpegmode = 1;
      }
   }
   $config = readConfig($config, CONFIG_FILE1);
   $config = readConfig($config, CONFIG_FILE2);
   $video_fps = $config['video_fps'];
   $divider = $config['divider'];
   $user = getUser();
   writeLog("Logged in user:" . $user . ":");
   $userLevel =  getUserLevel($user);
   writeLog("UserLevel " . $userLevel);
  ?>

<html>
   <head>
      <!--<meta name="viewport" content="width=550, initial-scale=1">-->
      <!--<title><?php echo CAM_STRING; ?></title>-->
      <link rel="stylesheet" href="css/style_minified.css" />
      <link rel="stylesheet" href="<?php echo getStyle(); ?>" />
      <meta charset="utf-8">
      <script src="js/style_minified.js"></script>
      <script src="js/script.js"></script>
      <script src="js/pipan.js"></script>
      <title>INSECT Robot Control Panel</title>
      <link rel="stylesheet" type="text/css" href="css/control.css">
      <script type="text/javascript" src="janus.js" ></script>
      <script type="text/javascript" src="streamingtest.js"></script>
      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/webrtc-adapter/6.0.3/adapter.min.js" ></script>
      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.7.2/jquery.min.js" ></script>
      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.0.2/js/bootstrap.min.js"></script>
      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.1.0/bootbox.min.js"></script>
      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.min.js"></script>
      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
      <script type="text/javascript" src="janus.js" ></script>
      <script type="text/javascript" src="streamingtest.js"></script>
      <script>
	$(function() {
		$(".navbar-static-top").load("navbar.html", function() {
			$(".navbar-static-top li.dropdown").addClass("active");
			$(".navbar-static-top a[href='streamingtest.html']").parent().addClass("active");
		});
		$(".footer").load("footer.html");
	});
      </script>
      <script type="text/javascript">
      $(document).ready( function() { $(function() { $("#start").click(); }); });
      </script>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/3.3.7/cerulean/bootstrap.min.css" type="text/css"/>
      <link rel="stylesheet" href="css/demo.css" type="text/css"/>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.2/css/font-awesome.min.css" type="text/css"/>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.css"/>
      <input id="recordchecker" name=recordchecker type=button style="display: none;" value="no">
   </head>
   <body onload="setTimeout('init(<?php echo "$mjpegmode, $video_fps, $divider" ?>);', 100);">
    <iframe name="poster" style="display: none;"></iframe>
    <div class=text1 style="background-color: rgb(255, 170, 0);">
      <p style="float: left;"><img src="ehtec.svg.png" alt="ehtec logo" height=230></p>
      <h2 style="color: rgb(220,20,60);">INSECT Robot Control Panel</h2>
      <p>Written by Elias Hohl and Maximillian Philipp<br>
      Copyright 2019 ehtec<br>
      This document is released under the GNU public license.<br>
      Many Thanks to silvanmelchior and meetecho, their code has been included in the project.<br>
      For further information, visit our homepage: <a href="https://www.ehtec.co" target="_blank">https://www.ehtec.co</a></p>
    </div>
    <br>
    <div class="panel panel-default" style="clear: left; margin: 10px;">
      <div class="panel-heading" style="text-align: center;"><span class=text2>CAMERA INTERFACE</span></div>
      <div class="panel-body">
        <form action="poster.php" method="post" target="poster">
          <center><input class=button5 style="background-color: rgb(255, 255, 0)" id="thecamera" name="camera=0" type="submit" value="activate camera"></center>
          <script type="text/javascript">
            function camFunction() {
              var rpicamwebinterface=document.getElementById("rpicamwebinterface");
              if (this.value == "activate camera") {
                this.value = "deactivate camera";
                this.name = "camera=0";
                rpicamwebinterface.style.display="inline";
              } else {
                this.value = "activate camera";
                this.name = "camera=1";
                rpicamwebinterface.style.display="none";
              }
            }
            var thecamera = document.getElementById("thecamera");
            thecamera.onclick = camFunction;
          </script>
        </form>
      </div>
    </div>
<!--here comes the div which is only shown when camera activated-->
   <div id="rpicamwebinterface" style="clear: left; display: none;">
      <div class="navbar navbar-inverse navbar-fixed-top" role="navigation" <?php getdisplayStyle('navbar', $userLevel); ?>>
         <div class="container">
            <div class="navbar-header">
               <a class="navbar-brand" href="#"><?php echo CAM_STRING; ?></a>
            </div>
         </div>
      </div>
	  <?php simple_button(); ?>
      <div class="container-fluid text-center liveimage">
         <div><img style="width: 90%;" id="mjpeg_dest" <?php echo getLoadClass() . getImgWidth();?>
		 <?php if(file_exists("pipan_on")) echo "ontouchstart=\"pipan_start()\""; ?> onclick="toggle_fullscreen(this);" src="./loading.jpg"></div>
         <div id="main-buttons">
            <input id="video_button" type="button" class="btn btn-primary" <?php getdisplayStyle('actions', $userLevel); ?>>
            <input id="image_button" type="button" class="btn btn-primary" <?php getdisplayStyle('actions', $userLevel); ?>>
            <input id="timelapse_button" type="button" class="btn btn-primary" <?php getdisplayStyle('actions', $userLevel); ?>>
            <input id="md_button" type="button" class="btn btn-primary" <?php getdisplayStyle('settings', $userLevel); ?>>
            <input id="halt_button" type="button" class="btn btn-danger" <?php getdisplayStyle('settings', $userLevel); ?>>
         </div>
      </div>
      <div id="secondary-buttons" class="container-fluid text-center">
         <?php pan_controls(); ?>
         <?php user_buttons(); ?>
         <a href="preview.php" class="btn btn-default" <?php getdisplayStyle('preview', $userLevel); ?>>Download Videos and Images</a>
         &nbsp;&nbsp;
         <?php  if($config['motion_external'] == '1'): ?><a href="motion.php" class="btn btn-default" <?php getdisplayStyle('settings', $userLevel); ?>>Edit motion settings</a>&nbsp;&nbsp;<?php endif; ?>
         <a href="schedule.php" class="btn btn-default" <?php getdisplayStyle('settings', $userLevel); ?>>Edit schedule settings</a>
      </div>
    
      <div class="container-fluid text-center">
         <div class="panel-group" id="accordion" <?php getdisplayStyle('settings', $userLevel); ?> >
            <div class="panel panel-default">
               <div class="panel-heading">
                  <h2 class="panel-title">
                     <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">Camera Settings</a>
                  </h2>
               </div>
               <div id="collapseOne" class="panel-collapse collapse">
                  <div class="panel-body">
                     <table class="settingsTable">
                        <tr>
                           <td>Resolutions:</td>
                           <td>Load Preset: <select onchange="set_preset(this.value)">
								<?php if(!file_exists('uPresets.html')) : ?>
                                 <option value="1920 1080 25 25 2592 1944">Select option...</option>
                                 <option value="1920 1080 25 25 2592 1944">Full HD 1080p 16:9</option>
                                 <option value="1280 0720 25 25 2592 1944">HD-ready 720p 16:9</option>
                                 <option value="1296 972 25 25 2592 1944">Max View 972p 4:3</option>
                                 <option value="768 576 25 25 2592 1944">SD TV 576p 4:3</option>
                                 <option value="1920 1080 01 30 2592 1944">Full HD Timelapse (x30) 1080p 16:9</option>
								 <?php else : include 'uPresets.html'; endif; ?>
                              </select><br>
                              Custom Values:<br>
                              Video res: <?php makeInput('video_width', 4, null, 'number'); ?>x<?php makeInput('video_height', 4, null, 'number'); ?>px<br>
                              Video fps: <?php makeInput('video_fps', 3, null, 'number'); ?>recording, <?php makeInput('MP4Box_fps', 3, null, 'number'); ?>boxing<br>
                              Image res: <?php makeInput('image_width', 4, null, 'number'); ?>x<?php makeInput('image_height', 4, null, 'number'); ?>px<br>
                              <input type="button" value="OK" onclick="set_res();">
                           </td>
                        </tr>
                        <?php  if($config['camera_num'] > 0): ?> 
                        <tr>
                           <td>Camera select (Compute module only)</td>
                           <td>
                              Use camera: <select onchange="send_cmd('cn ' + this.value)"><?php makeOptions($options_cn, 'camera_num'); ?></select>
                           </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                           <td>Timelapse-Interval (0.1...3200):</td>
                           <td><?php makeInput('tl_interval', 4, null, 'number'); ?>s <input type="button" value="OK" onclick="send_cmd('tv ' + 10 * document.getElementById('tl_interval').value)"></td>
                        </tr>
                        <tr>
                           <td>Video Split (seconds, default 0=off):</td>
                           <td><?php makeInput('video_split', 6, null, 'number'); ?>s <input type="button" value="OK" onclick="send_cmd('vi ' + document.getElementById('video_split').value)"></td>
                        </tr>
                        <tr>
                           <td>Annotation (max 127 characters):</td>
                           <td>
                              Text: <?php makeInput('annotation', 20); ?><input type="button" value="OK" onclick="send_cmd('an ' + encodeURI(document.getElementById('annotation').value))"><input type="button" value="Default" onclick="document.getElementById('annotation').value = 'RPi Cam %Y.%M.%D_%h:%m:%s'; send_cmd('an ' + encodeURI(document.getElementById('annotation').value))"><br>
                              Background: <select onchange="send_cmd('ab ' + this.value)"><?php makeOptions($options_ab, 'anno_background'); ?></select>
                           </td>
                        </tr>
                        <tr>
                           <td>Annotation size(0-99):</td>
                           <td>
                              <?php makeInput('anno_text_size', 3, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('as ' + document.getElementById('anno_text_size').value)">
                           </td>
                        </tr>
                        <tr>
                           <td>Custom text color:</td>
                           <td><select id="at_en"><?php makeOptions($options_at_en, 'anno3_custom_text_colour'); ?></select>
                              y:u:v = <?php makeInput('at_y', 3, 'anno3_custom_text_Y'); ?>:<?php makeInput('at_u', 4, 'anno3_custom_text_U'); ?>:<?php makeInput('at_v', 4, 'anno3_custom_text_V'); ?>
                              <input type="button" value="OK" onclick="set_at();">
                           </td>
                        </tr>
                        <tr>
                           <td>Custom background color:</td>
                           <td><select id="ac_en"><?php makeOptions($options_ac_en, 'anno3_custom_background_colour'); ?></select>
                              y:u:v = <?php makeInput('ac_y', 3, 'anno3_custom_background_Y'); ?>:<?php makeInput('ac_u', 4, 'anno3_custom_background_U'); ?>:<?php makeInput('ac_v', 4, 'anno3_custom_background_V'); ?>
                              <input type="button" value="OK" onclick="set_ac();">
                           </td>
                           </tr>
                        <tr>
                        <?php if (file_exists("pilight_on")) pilight_controls(); ?>
                        <tr>
                           <td>Buffer (1000... ms), default 0:</td>
                           <td><?php makeInput('video_buffer', 4, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('bu ' + document.getElementById('video_buffer').value)"></td>
                        </tr>                        <tr>
                           <td>Sharpness (-100...100), default 0:</td>
                           <td><?php makeInput('sharpness', 4, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('sh ' + document.getElementById('sharpness').value)"></td>
                        </tr>
                        <tr>
                           <td>Contrast (-100...100), default 0:</td>
                           <td><?php makeInput('contrast', 4, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('co ' + document.getElementById('contrast').value)">
                           </td>
                        </tr>
                        <tr>
                           <td>Brightness (0...100), default 50:</td>
                           <td><?php makeInput('brightness', 4, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('br ' + document.getElementById('brightness').value)"></td>
                        </tr>
                        <tr>
                           <td>Saturation (-100...100), default 0:</td>
                           <td><?php makeInput('saturation', 4, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('sa ' + document.getElementById('saturation').value)"></td>
                        </tr>
                        <tr>
                           <td>ISO (100...800), default 0:</td>
                           <td><?php makeInput('iso', 4, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('is ' + document.getElementById('iso').value)"></td>
                        </tr>
                        <tr>
                           <td>Metering Mode, default 'average':</td>
                           <td><select onchange="send_cmd('mm ' + this.value)"><?php makeOptions($options_mm, 'metering_mode'); ?></select></td>
                        </tr>
                        <tr>
                           <td>Video Stabilisation, default: 'off'</td>
                           <td><select onchange="send_cmd('vs ' + this.value)"><?php makeOptions($options_vs, 'video_stabilisation'); ?></select></td>
                        </tr>
                        <tr>
                           <td>Exposure Compensation (-10...10), default 0:</td>
                           <td><?php makeInput('exposure_compensation', 4, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('ec ' + document.getElementById('exposure_compensation').value)"></td>
                        </tr>
                        <tr>
                           <td>Exposure Mode, default 'auto':</td>
                           <td><select onchange="send_cmd('em ' + this.value)"><?php makeOptions($options_em, 'exposure_mode'); ?></select></td>
                        </tr>
                        <tr>
                           <td>White Balance, default 'auto':</td>
                           <td><select onchange="send_cmd('wb ' + this.value)"><?php makeOptions($options_wb, 'white_balance'); ?></select></td>
                        </tr>
                        <tr>
                           <td>White Balance Gains (x100):</td>
                           <td> gain_r <?php makeInput('ag_r', 4, 'autowbgain_r', 'number'); ?> gain_b <?php makeInput('ag_b', 4, 'autowbgain_b', 'number'); ?>
                              <input type="button" value="OK" onclick="set_ag();">
                           </td>
                        </tr>
                        <tr>
                           <td>Image Effect, default 'none':</td>
                           <td><select onchange="send_cmd('ie ' + this.value)"><?php makeOptions($options_ie, 'image_effect'); ?></select></td>
                        </tr>
                        <tr>
                           <td>Colour Effect, default 'disabled':</td>
                           <td><select id="ce_en"><?php makeOptions($options_ce_en, 'colour_effect_en'); ?></select>
                              u:v = <?php makeInput('ce_u', 4, 'colour_effect_u'); ?>:<?php makeInput('ce_v', 4, 'colour_effect_v'); ?>
                              <input type="button" value="OK" onclick="set_ce();">
                           </td>
                        </tr>
                        <tr>
                           <td>Image Statistics, default 'Off':</td>
                           <td><select onchange="send_cmd('st ' + this.value)"><?php makeOptions($options_st, 'stat_pass'); ?></select></td>
                        </tr>
                        <tr>
                           <td>Rotation, default 0:</td>
                           <td><select onchange="send_cmd('ro ' + this.value)"><?php makeOptions($options_ro, 'rotation'); ?></select></td>
                        </tr>
                        <tr>
                           <td>Flip, default 'none':</td>
                           <td><select onchange="send_cmd('fl ' + this.value)"><?php makeOptions($options_fl, 'flip'); ?></select></td>
                        </tr>
                        <tr>
                           <td>Sensor Region, default 0/0/65536/65536:</td>
                           <td>
                              x: <?php makeInput('roi_x', 5, 'sensor_region_x', 'number'); ?> y: <?php makeInput('roi_y', 5, 'sensor_region_y', 'number'); ?><br>
                              w: <?php makeInput('roi_w', 5, 'sensor_region_w', 'number'); ?> h:  <?php makeInput('roi_h', 5, 'sensor_region_h', 'number'); ?>
                              <input type="button" value="OK" onclick="set_roi();">
                           </td>
                        </tr>
                        <tr>
                           <td>Shutter speed (0...330000), default 0:</td>
                           <td><?php makeInput('shutter_speed', 4, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('ss ' + document.getElementById('shutter_speed').value)">
                           </td>
                        </tr>
                        <tr>
                           <td>Image quality (0...100), default 10:</td>
                           <td>
                              <?php makeInput('image_quality', 4, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('qu ' + document.getElementById('image_quality').value)">
                           </td>
                        </tr>
                        <tr>
                           <td>Preview quality (1...100), default 10:<br>Width (128...1024), default 512:<br>Divider (1-16), default 1:</td>
                           <td>
                              Quality: <?php makeInput('quality', 4); ?><br>
                              Width: <?php makeInput('width', 4); ?><br>
                              Divider: <?php makeInput('divider', 4); ?><br>
                              <input type="button" value="OK" onclick="set_preview();">
                           </td>
                        </tr>
                        <tr>
                           <td>Raw Layer, default: 'off'</td>
                           <td><select onchange="send_cmd('rl ' + this.value)"><?php makeOptions($options_rl, 'raw_layer'); ?></select></td>
                        </tr>
                        <tr>
                           <td>Video bitrate (0...25000000), default 17000000:</td>
                           <td>
                              <?php makeInput('video_bitrate', 10, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('bi ' + document.getElementById('video_bitrate').value)">
                           </td>
                        </tr>
                        <tr>
                           <td>Mininimise frag (0/1), default 0:<br>Init Quantisation, default 25:<br>Encoding qp, default 31:</td>
                           <td>
                              MF: <?php makeInput('minimise_frag', 4, null, 'number'); ?><br>
                              IQ: <?php makeInput('initial_quant', 4, null, 'number'); ?><br>
                              QP: <?php makeInput('encode_qp', 4, null, 'number'); ?><br>
                              <input type="button" value="OK" onclick="set_encoding();">
                           </td>
                        </tr>
                        <tr>
                           <td>MP4 Boxing mode :</td>
                           <td><select onchange="send_cmd('bo ' + this.value)"><?php makeOptions($options_bo, 'MP4Box'); ?></select></td>
                        </tr>
                        <tr>
                           <td>Watchdog, default interval 3s, errors 3s:</td>
                           <td>Interval <?php makeInput('watchdog_interval', 3, null, 'number'); ?>s&nbsp;&nbsp;&nbsp;&nbsp;Errors <?php makeInput('watchdog_errors', 3, null, 'number'); ?>
                           <input type="button" value="OK" onclick="send_cmd('wd ' + 10 * document.getElementById('watchdog_interval').value + ' ' + document.getElementById('watchdog_errors').value)">
                           </td>
                        </tr>
                        <tr>
                           <td>Motion detect mode:</td>
                           <td><select onchange="send_cmd('mx ' + this.value);setTimeout(function(){location.reload(true);}, 1000);"><?php makeOptions($options_mx, 'motion_external'); ?></select></td>
                        </tr>
                        <tr>
                           <td>Log size lines, default 5000:</td>
                           <td>
                              <?php makeInput('log_size', 6, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('ls ' + document.getElementById('log_size').value)">
                           </td>
                        </tr>
                     </table>
                  </div>
               </div>
            </div>
            <div class="panel panel-default" <?php  if($config['motion_external'] == '1') echo "style ='display:none;'"; ?>>
               <div class="panel-heading">
                  <h2 class="panel-title">
                     <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">Motion Settings</a>
                  </h2>
               </div>
               <div id="collapseTwo" class="panel-collapse collapse">
                  <div class="panel-body">
                     <table class="settingsTable">
                        <tr>
                          <td>Motion Vector Preview:</td>
                          <td>
                            <select onchange="send_cmd('vp ' + this.value);setTimeout(function(){location.reload(true);}, 1000);" id="preview_select"><?php makeOptions($options_vp, 'vector_preview'); ?></select>
                          </td>
                        </tr>
                        <tr>
                           <td>Noise level (1-255 / >1000):</td>
                           <td>
                              <?php makeInput('motion_noise', 5, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('mn ' + document.getElementById('motion_noise').value)">
                           </td>
                        </tr>
                        <tr>
                           <td>Threshold (1-32000):</td>
                           <td>
                              <?php makeInput('motion_threshold', 5, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('mt ' + document.getElementById('motion_threshold').value)">
                           </td>
                        </tr>
                        <tr>
                           <td>Clipping factor (2-50), default 3:</td>
                           <td>
                              <?php makeInput('motion_clip', 5, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('mc ' + document.getElementById('motion_clip').value)">
                           </td>
                        </tr>
                        <tr>
                           <td>Mask Image:</td>
                           <td>
                              <?php makeInput('motion_image', 30); ?><input type="button" value="OK" onclick="send_cmd('mi ' + document.getElementById('motion_image').value)">
                           </td>
                        </tr>
                        <tr>
                           <td>Delay Frames to detect:</td>
                           <td>
                              <?php makeInput('motion_initframes', 5, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('ms ' + document.getElementById('motion_initframes').value)">
                           </td>
                        </tr>
                        <tr>
                           <td>Change Frames to start:</td>
                           <td>
                              <?php makeInput('motion_startframes', 5, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('mb ' + document.getElementById('motion_startframes').value)">
                           </td>
                        </tr>
                        <tr>
                           <td>Still Frames to stop:</td>
                           <td>
                              <?php makeInput('motion_stopframes', 5, null, 'number'); ?><input type="button" value="OK" onclick="send_cmd('me ' + document.getElementById('motion_stopframes').value)">
                           </td>
                        </tr>
                        <tr>
                           <td>Save vectors to .dat:<br>(Uses more space)</td>
                           <td><select onchange="send_cmd('mf ' + this.value);"><?php makeOptions($options_mf, 'motion_file'); ?></select></td>
                        </tr>
                     </table>
                  </div>
               </div>
            </div>
            <div class="panel panel-default">
               <div class="panel-heading">
                  <h2 class="panel-title">
                     <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">System</a>
                  </h2>
               </div>
               <div id="collapseThree" class="panel-collapse collapse">
                  <div class="panel-body">
                     <input id="toggle_stream" type="button" class="btn btn-primary" value="<?php echo $streamButton; ?>" onclick="set_stream_mode(this.value);">
                     <input id="allow_simple" type="button" class="btn btn-primary" value="<?php echo $allowSimple; ?>" onclick="set_display(this.value);">
                     <input id="shutdown_button" type="button" value="shutdown system" onclick="sys_shutdown();" class="btn btn-danger">
                     <input id="reboot_button" type="button" value="reboot system" onclick="sys_reboot();" class="btn btn-danger">
                     <input id="reset_button" type="button" value="reset settings" onclick="if(confirm('Are you sure to reset the settings to the default values?')) {send_cmd('rs 1');setTimeout(function(){location.reload(true);}, 1000);}" class="btn btn-danger">
                     <form action='<?php echo ROOT_PHP; ?>' method='POST'>
                        <br>Style
                        <select name='extrastyle' id='extrastyle'>
                           <?php getExtraStyles(); ?>
                        </select>
                        &nbsp;<button type="submit" name="OK" value="OK" >OK</button>
                     </form>
					 Set Date/Time <input type='text' size=20 id='timestr' value='13 FEB 2018 12:00:00'><input type="button" value="OK" onclick="sys_settime();"<BR>
					 <table class="settingsTable">
						<?php macroUpdates(); ?>
					 </table>
                  </div>
               </div>
            </div>
            <div class="panel panel-default">
               <div class="panel-heading">
                  <h2 class="panel-title">
                     <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour">Help</a>
                  </h2>
               </div>
               <div id="collapseFour" class="panel-collapse collapse">
                  <div class="panel-body">
                    Github: <a href="https://github.com/silvanmelchior/RPi_Cam_Web_Interface" target="_blank">https://github.com/silvanmelchior/RPi_Cam_Web_Interface</a><br>
                    Forum: <a href="http://www.raspberrypi.org/forums/viewtopic.php?f=43&t=63276" target="_blank">http://www.raspberrypi.org/forums/viewtopic.php?f=43&t=63276</a><br>
                    Wiki: <a href="http://elinux.org/RPi-Cam-Web-Interface" target="_blank">http://elinux.org/RPi-Cam-Web-Interface</a>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <?php if ($debugString != "") echo "$debugString<br>"; ?>
   </div>
<!--here ends the div only shown when camera activated-->
<div class="panel panel-default" style="clear: left; margin: 10px;">
      <div class="panel-heading" style="text-align: center;"><span class=text2>GENERAL MOVEMENT CONTROL</span></div>
    <div class="panel-body">
      <form action="poster.php" method="post" target="poster">
        <input class=button1 style="float: left; color: green;" name="forward" type="submit" value="forward">
        <input class=button1 style="float: right; color: red;" name="stop" type="submit" value="stop">
        </input>
      </form>
    </div>
    </div>
    <center><div style="clear: left;">
      <input class=button6 name="hidebutton" id="hidebutton" type="button" value="show advanced options">
      <script type="text/javascript">
        function hideFunction() {
          var hidediv = document.getElementById("advancedopts");
          if (this.value == "show advanced options") {
            this.value = "hide advanced options";
            hidediv.style.display = "inline";
          } else {
            this.value = "show advanced options";
            hidediv.style.display = "none";
          }
        }
        var hidebutton = document.getElementById("hidebutton");
        hidebutton.onclick = hideFunction;
      </script>
    </div></center>
    <div style="display: none;" id="advancedopts">
    <div class="panel panel-default" style="clear: left; margin: 10px;">
      <div class="panel-heading" style="text-align: center;"><span class=text2>PLAY AUDIO</span></div>
      <div class="panel-body">
      <form action="poster.php" method="post" target="poster">
        <input class=button2 style="float: left; color: black;" name="play=helterskelter" type="submit" value="Helter Skelter">
        <input class=button2 style="float: right; color: black;" name="play=bundeshymne" type="submit" value="Bundeshymne">
        <br><input class=button2 style="float: left; color: black;" name="play=faust" type="submit" value="Faust">
        <input class=button2 style="float: right; color: black;" name="play=daedalus" type="submit" value="Daedalus">
        <br><center><input class=button2 style="color: black; background-color: rgb(205, 92, 92); width: 60%;" name="stopsound" type="submit" value="stop all sounds"></center>
      </form>
    </div>
    </div>
    <div class="panel panel-default" style="clear: left; margin: 10px;">
    <div class="panel-heading" style="text-align: center;"><span class=text2>VOLUME ADJUSTING</span></div>
    <div class="panel-body">
    <div class="slidecontainer" style="clear: left;">
      <br><center><input type="range" min="0" max="100" value="50" class="slider" id="volumectrl">
      <form action="poster.php" method="post" target="poster">
        <span class=text3>VOLUME: </span><span class=text3 id="demo"></span><span class=text3>%</span><input class=button3 style="float: right; color: black;" id="changevol" name="changevol" type="submit" value="change volume">
      </form>
      <script type="text/javascript">
        var slider = document.getElementById("volumectrl");
        var output = document.getElementById("demo");
        var button = document.getElementById("changevol");
        output.innerHTML = slider.value;
        button.name = "changevol="+slider.value;
        slider.oninput = function() {
          output.innerHTML = this.value;
          button.name = "changevol=" + this.value;
        }
      </script>
    </div></center>
    </div>
    </div>
    <div class="panel panel-default" style="clear: left; margin: 10px;">
      <div class="panel-heading" style="text-align: center;"><span class=text2>STREAMING</span></div>
      <div class="panel-body">
      <form action="poster.php" method="post" target="poster">
        <input id="playstream" name="playstream" style="float: left;" type="button" class=button4 value="start microphone stream" />
        <script type="text/javascript">
         //function sleep(ms) {
         //   return new Promise(resolve => setTimeout(resolve, ms));
         // }
          function doFunction() {
            var recordchecker = document.getElementById("recordchecker");
            if (this.value == "start microphone stream") {
              //$("#start").click();
              //await sleep(1000);
              recordchecker.value = "yes";
              $("#watch").click();
              this.value = "stop microphone stream";
            } else {
              recordchecker.value = "no";
              $("#watch").click();
              this.value = "start microphone stream";
            }
          }
          var streambutton = document.getElementById("playstream");
          streambutton.onclick = doFunction;
        </script>
        <input class=button4 style="float: right;" id="recorder" name="startrecording" type="button" value="start recording">
        <script type="text/javascript">
        function recFunction() {
          var recordchecker = document.getElementById("recordchecker");
          if (recordchecker.value == "yes") {
            $("#watch").click();
          }
          if (this.value == "start recording") {
            this.value = "stop recording & play";
            document.getElementById("recordButton").click();
            } else {
            this.value = "start recording";
            document.getElementById("stopButton").click();
            }
          }
        var recorderbutton = document.getElementById("recorder");
        recorderbutton.onclick = recFunction;
        </script>
      </form>
    </div>
    </div>
    <div class="panel panel-default" style="clear: left;">
      <div class="panel-heading" style="text-align: center; margin: 10px;"><span class=text2>LANGUAGE CONTROL</span></div>
      <div class="panel-body">
      <form action="poster.php" method="post" target="poster">
        <center><input class=button5 name="langctrl" id="langctrl" type="submit" value="activate language control"></center>
        <script type="text/javascript">
          function dooFunction() {
            if (this.value == "activate language control") {
              this.value = "deactivate language control";
              this.name = "langctrl=0";
            } else {
              this.value = "activate language control";
              this.name = "langctrl=1";
            }
          }
          var langbutton = document.getElementById("langctrl");
          langbutton.onclick = dooFunction;
        </script>
      </form>
    </div>
    </div>
    <div class="panel panel-default" style="clear: left;">
      <div class="panel-heading" style="text-align: center; margin: 10px;"><span class=text2>SPOKEN FEEDBACK</span></div>
      <div class="panel-body">
        <center><select class=custom-select id="feedbacklevel" name="FEEDBACK LEVEL">
        <option value='-1'>no spoken feedback</option>
        <option value='0' selected>spoken feedback for language control commands</option>
        <option value='1'>always spoken feedback</option>
        </select></center>
      <script type=text/javascript>
        function doChFeedback() {
          var thevalue= this.options[this.selectedIndex].value;
          $.ajax({
            url: "poster.php",
            type: "POST",
            data: encodeURIComponent("feedbacklevel="+thevalue),
            success: function(data) {
            }
          });
        }
        var feedbackchanger=document.getElementById("feedbacklevel");
        feedbackchanger.onchange = doChFeedback;
      </script>
      </div>
    </div>
    <div class="panel panel-default" style="clear: left;">
      <div class="panel-heading" style="text-align: center; margin: 10px;"><span class=text2>SYSTEM</span></div>
      <div class="panel-body">
        <form action="poster.php" method="POST" target="poster">
          <input class=button7 type="submit" style="float: left;" name="shutdown" value="shutdown">
          <input class=button7 type="submit" style="float: right;" name="reboot" value="reboot">
        </form>
      </div>
    </div>
</div>

<!--here comes meetechos copied code-->

<div class="container" style="clear: left; display: none;">
	<div class="row">
		<div class="col-md-12">
			<div class="page-header">
				<h1>Plugin Demo: Streaming
					<button autocomplete="off" id="start">Start</button>
				</h1>
			</div>
			<div id="streams">
				<div class="row">
					<div class="col-md-6">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h3 class="panel-title">Streams <i id="update-streams" class="fa fa-refresh" title="Update list of streams" style="cursor: pointer;"></i></h3>
							</div>
							<div class="panel-body" id="list">
								<div class="btn-group btn-group-sm">
									<button class="btn btn-primary" autocomplete="off" id="watch">Watch or Listen</button>
									<div class="btn-group btn-group-sm">
										<button autocomplete="off" id="streamset" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
											Streams list<span class="caret"></span>
										</button>
										<ul id="streamslist" class="dropdown-menu" role="menu">
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h3 class="panel-title">Stream
									<span class="label label-info hide" id="status"></span>
									<span class="label label-primary hide" id="curres"></span>
									<span class="label label-info hide" id="curbitrate"></span>
								</h3>
							</div>
							<div class="panel-body" id="stream"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<hr>
</div>

<!--here ends meetechos copied code-->

<!--here comes recording copied code-->
<div class="container" style="display: none;">
<div id="controls" style="clear: left;">
  	 <button id="recordButton">Record</button>
  	 <button id="pauseButton" disabled>Pause</button>
  	 <button id="stopButton" disabled>Stop</button>
    </div>
    <div id="formats">Format: start recording to see sample rate</div>
  	<h3>Recordings</h3>
  	<ol id="recordingsList"></ol>
</div>

    <!-- inserting these scripts at the end to be able to use all the elements in the DOM -->
  	<script src="https://cdn.rawgit.com/mattdiamond/Recorderjs/08e7abd9/dist/recorder.js"></script>
  	<script src="js/app.js"></script>
<!--here ends recording copied code-->


  </body>
</html>

<!--php submit code moved to poster.php-->

