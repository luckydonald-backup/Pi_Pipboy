<!DOCTYPE html>
<html lang="en" >

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">  
  <title>Lazer Tag Pip-Boy UI</title>
  <link rel="stylesheet" type="text/css" href="css/pip.css">
<?php
  include "common.inc";
  include "common.php";
    
  $OwnerId        = getParam ("OwnerId" );
  $Owner          = findOwner ( $OwnerId );
  $health         = $Owner ["Health"];
  $UserName       = $Owner ["Username"];
  $StimpakId      = findItem ( "Stimpak");
  $ItemId         = $StimpakId["ID"];
  $StimPaks       = findInventory ($OwnerId, $ItemId);
  $stimQuantity   = $StimPaks["Quantity"]; 
  $ReloadId       = findItem ("Reload");
  $ItemId         = $ReloadId ["ID"];
  $Reloads        = findInventory ($OwnerId, $ItemId);
  $reloadQuantity = $Reloads["Quantity"];
?>
  
</head>

<body>
<div class="screen">
  <div class="screen-reflection"></div>
  <div class="scan"></div>
  <nav class="holder">
     <span class="stat"  onclick="activate( 'stat');"></span>
     <span class="inv"   onclick="activate(  'inv');"></span>
     <span class="data"  onclick="activate( 'data');"></span>
     <span class="map"   onclick="activate(  'map');"></span>
     <span class="radio" onclick="activate('radio');"></span>
     <p>
     <span class="status"></span><span class="off special"></span></p>
  </nav>
  <div class="radioBoy" id="radioDiv" >
     <img src="images/radio.jpg" style="position:absolute" width="300px">
  </div>
  <div class="invBoy" id="invDiv">
     <img src="images/inventory.png" width="304px"      style="position:absolute;left:5px;top:50px">
  </div>
  <div class="dataBoy" id="dataDiv">
    <img src="images/data.jpg" width="300px"  style="position:absolute;top:50px">
  </div>
  <div class="mapBoy" id="mapDiv">
    <img src="images/map.png" width="304px" style="position:absolute">
  </div>
  <div class="vaultboy" id="boyDiv">
    <!-- health bars -->
    <div class="bar1"></div>
    <div class="bar2"></div>
    <div class="bar3"></div>
    <div class="bar4"></div>
    <div class="bar5"></div>
    <div class="bar6"></div>
  </div>
  <div id="statIcons">
     <div class="username">     
     <?php
        echo ("<span>$UserName</span>\n");
     ?>
     </div>
     <div class="supplies"><span onclick="stimpakClick();" id="stimpak">     
     <?php
     echo ("Stimpak($stimQuantity)");
     ?>
     </span><span>Radaway (0)</span><span1></span1>
     <span onclick="reloadClick();" id="reload">
     <?php
        echo ("Reloads($reloadQuantity)");
     ?>
     </span></div>
     <div class="info-bar">
       <span class="weapon"></span>
       <span class="aim"><p>21</p></span>
       <span class="helmet"></span>
       <span class="shield"><p>114</p></span>
       <span class="voltage"><p>126</p></span>
       <span class="nuclear"><p>35</p></span>
     </div>
     <div class="hud-bar">
       <div class="hp"></div>
       <div class="exp"></div>
       <div class="ap"></div>
     </div>
  </div>
</div>
</body>
</html>
<script>

<?php
  echo ("var ownerId        = $OwnerId;\n" );
  echo ("var health         = $health;\n");
  echo ("var userName       = \"$UserName\";\n" );
  if ($stimQuantity == "") { 
     echo ("var stimQuantity = 0; // TODO: unknown stimQuantity (due to player die?)\n" );
  } else {      
     echo ("var stimQuantity   = $stimQuantity;\n" );
  }

  if ($reloadQuantity == "") {   
     echo ("var reloadQuantity = 0; // TODO: unknown reloadQuantity (due to player die?)\n" );
     echo ("var ammunition = 0;\n" );
  } else {      
     echo ("var reloadQuantity = $reloadQuantity;\n" );
     echo ("var ammunition     = $reloadQuantity * 25;\n" );
  }   
  
  echo ("/* Debug Info \n" );
  echo (" StimpakId: $ItemId\n" );
  echo ("*/\n" );
?>
  setTimeout(function(){window.location.reload(1);}, 3000); // Refresh every three seconds
</script>  

<script src="js/pip.js"></script>
</body>
</html>
