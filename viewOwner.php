<html>
<head> <Title>View Owner</Title>
<?php
  include "common.inc";
  include "common.php";
  $OwnerId = getParam ("OwnerId" );
  $Owner = findOwner ( $OwnerId);
  $Name =   $Owner ["Username"];
  $Health = $Owner ["Health"];
  $MAC = $Owner["MAC"];
  $Hits = $Owner["Hits"];
  //$Ammo = $Owner["Ammo"];
  $MACOwner = $Owner["MACOwner"];
  $Typename = $Owner["Typename"];
  $Team = $Owner["Team"];
?>
<meta charset="utf-8" />'
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
html {
  background: url("images/pipboyBackground.jpg") no-repeat fixed;
  background-size: cover;
}
body {
  color: white;
}
</style>
</head>
<script>
  setTimeout(function(){window.location.reload(1);}, 3000); // Refresh every three seconds
</script>
<body>
<?php
  // Show Hits and Health
  echo ("<H2><Font color=\"$Team\">$Name</Font> Health:$Health Hits:$Hits</H2>\n");
  showFlagsTable();
  showStimpaks($OwnerId);
  showReloads($OwnerId);
  echo ("<input type=\"button\" value=\"inventory\" onclick=\"window.location.href='androidViewer.php?OwnerId=$OwnerId';\"><BR>\n" );
  echo ("<input type=\"button\" value=\"View Profile\" onclick=\"window.location.href='viewProfile.php?OwnerId=$OwnerId';\"><br>\n" ); 
  
?>
<br>
<input type="button" value="back" onclick="window.location.href='index.php';">
</body>
</html>