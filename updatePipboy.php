<html>
<body>
<?php
include "common.inc";
include "common.php";

$MAC = getParam ("MAC");
echo ("MAC: $MAC");
$Pipboy = findPipboy ($MAC);
$PipboyTypename = $Pipboy["Typename"];
$Username = $Pipboy["Username"];
$IpAddress = $_SERVER['REMOTE_ADDR'];  

$Message = getParam ("Message");
echo ("Message: $Message going to IpAddress: $IpAddress<br>\n");
list($Command, $Parameter, $data) = explode(":", $Message);
$cmd = strtolower ($Command);  // lower case command
echo ( "Command: $Command Parameter:$Parameter data:$data<br>\n");

// Handle the Command
if ($cmd == "broadcast") {
   $result = query ( "Select * From pipboys" );
   $count = 0;
   $FromPlayer = $Pipboy["Username"];
   $msg = "($FromPlayer): $Parameter";
   while ($row = mysql_fetch_assoc ($result))   {		 
      $Id = $row["ID"];
      $Username = $row["Username"];
      $mac = $row["MAC"];
      $Destination = $row["IpAddress"];
      if ($mac != $MAC) { // Don't send message to self      
         $cmd = "python sendMessage.py $Destination \"$msg\"";
         echo ( "$cmd<BR>\n");  
	        exec($cmd);	       
      }  
   }
} elseif ($cmd == "add") { 
   $item = findItem ($Parameter); 
   $ItemId = $item["ID"];
   $ID = $Pipboy["ID"];
   $sql = "Select * From inventory where OwnerId=$ID and ItemId=$ItemId";
   echo ("<br>$sql<br>" );
   $Quantity = 0;
   $result = query ($sql);
   
   if ($row = mysql_fetch_assoc($result)) { // Owner already has at least one 
     $Quantity = $row["Quantity"];
     $Quantity = $Quantity +1;  
     $sql = "Update inventory set Quantity=$Quantity where OwnerId=$ID and ItemId=$ItemId";    
   } else {
     $sql = "Insert into inventory (OwnerId, ItemId, Quantity) Values ($ID, $ItemId, 1)";     
   }
   echo ("<br>$sql<br>" );   
   $result = query($sql);
   
} elseif ($cmd == "startgame") {
   captureTheFlags();
} elseif ($cmd == "ouch") {
   $ID     = $Pipboy ["ID"];
   $Health = $Pipboy ["Health"];
   $myTeam = $Pipboy ["Team"];
    
   $shooterFound = true;
   $shooterMAC = "";
   // Get shooterName and shooterTeam
   if (($Parameter == 'c00') || ($Parameter == 'abc')) {      
     $shooter = false;
     $shooterTeam = 'Green';    
     $shooterName = 'Sentry';
     echo ("Shot by Green sentry<br>\n" );
   } else if ($Parameter == 'c01' ) { 
     $shooter = false;
     $shooterTeam = 'Blue';
     $shooterName = 'Sentry';
     echo ("Shot by Blue sentry<br>\n" );
   } else if ($Parameter == 'c02' ) {
     $shooter = false;
     $shooterTeam = 'Red';
     $shooterName = 'Sentry';
     echo ("Shot by Red sentry<br>\n" );       
   } else {              
     $shooter = findShooter ( $Parameter);
     if ($shooter) { 
        $shooterName = $shooter["Username"];   
        $shooterTeam = $shooter["Team"];
        $shooterMAC =  $shooter["MAC"];
     } else {
        $shooterFound = false;
     }       
   }  
 
   if ($shooterFound) { 
      if ($shooterMAC == $MAC) { // stimpak 
         echo ("I have detected the use of a stimpak<br>\n" );
         $ID = $Pipboy["ID"];         
         useStimpak ($ID);                  
      } else if ($PipboyTypename == "Flag") {
         echo ("I am a flag at $IpAddress, getting hit by team: $shooterTeam<br>\n" );
         // Change command to set color
         $cmd = "python sendMessage.py $IpAddress \"$shooterTeam\"";
         echo ("Executing command: $cmd<br>\n" );
         exec($cmd);   
         if ($myTeam != $shooterTeam) { // Color is being changed
            $sql = "Insert into systemlog (Message) Values ('$shooterName turned a flag to color $shooterTeam!!' )";
            query ($sql);        
            incrementHits ($shooter, 1); // Add to leaderboard	
          
            $sql = "Update pipboys set Team='$shooterTeam' Where ID = $ID";
            $result = query ($sql);
            
            // Check all flags to see if the game is won yet.
            $sql = "Select * From pipboys where Typename='Flag'";
            $result = query ($sql);
            $lastTeam = "";
            $winner = 1;
            while ($row = mysql_fetch_assoc ($result)) {		 
               $Team = $row["Team"];
               if ($lastTeam == "") {
                  $lastTeam = $Team;
               } else if ($lastTeam != $Team) {
                  $winner = 0;
                  break;
               }
            }      

            // Todo: Use boolean for winner rather than integer   
            if ($winner == 1) { // Send "Winner message to make the flag blink" 
               blinkFlags ($Team);
                 
               $sql = "Insert into systemlog (Message) Values ('$shooterName won the game!!!' )";
               query ($sql);        
               incrementHits ($shooter, 20);  // Add to the leader board
               // Kill all players on other $Team(s).
               $sql = "Select * From pipboys";
               $result = query ($sql);
               while ($row = mysql_fetch_assoc ($result)) {		        
                  $Team = $row["Team"];
                  if ($Team != $lastTeam) { // This team has lost
                     $Destination = $row["IpAddress"];     
                     $ID = $row["ID"];
                     $cmd = "python sendMessage.py $Destination \"died\"";
                     exec($cmd);	
                     $sql = "Update pipboys set Health=0 where ID=$ID";          
                     $r = query ($sql);             
                  }  
               }                    
            }  
         }       
      } else { // Receiver is not a flag, could be a player      
        echo ("My current health is: $Health" );
        if ($Health == 0) {
           echo ("I is already dead" );
        } else if ( $myTeam == $shooterTeam ) {
           echo ("Ignore friendly fire" );    
        } else { // $Health is greater than zero and has been hit 
           $sql = "Insert into systemlog (Message) Values ('$shooterName shot $Username!' )";
           query ($sql);        
           
           if ($shooter) { 
             incrementHits ($shooter, 1);
           }         
           // Notify pipboy that they have been hit.         
           $Message = "hit";
           $cmd = "/usr/bin/python /var/www/html/Pipboy/sendMessage.py $IpAddress \"$Message\"";
           echo "<br>$cmd<br>\n";
           $mystring = exec($cmd, $output);
           echo "<br>$mystring<br>\n" ;
           var_dump ($output);
                          
           $Health = $Health - 1;         
           $sql = "Update pipboys Set Health=$Health Where ID=$ID";
           $result = query ($sql);
           $numTeams = numberOfTeams();
           echo ("The number of teams remaining: $numTeams <br>\n");
           
           if ($numTeams == 1) { 
              echo ("number of teams == 1, so there is a winner <BR>\n" );
              $team = findWinningTeam();
              blinkFlags ($team);           
           } 
           
           if ($Health==0) {            
             if ($shooter) { 
               echo ( "Give $shooterName all my stuff.<br>\n" );
               $sql = "Insert into systemlog (Message) Values ('$shooterName killed $Username and got all his stuff!' )";
               query ($sql);
                      
               $sql = "Select * From inventory where OwnerId=$ID";
               // Transfer all items to shooter
               $result = query ($sql);
               while ($row = mysql_fetch_assoc ($result))   {		 
                  $addQuantity = $row["Quantity"];
                  $shooterId = $shooter["ID"];
                  $ItemId = $row["ItemId"];
                  $shooterItem = findInventory ($shooterId,$ItemId);
                  if ($shooterItem) { 
                    $inventoryId = $shooterItem["ID"];
                    // Shooter already has item 
                    $Quantity = $shooterItem["Quantity"];                
                    $newQuantity = $Quantity + $addQuantity;
                    echo ("Old quantity: $Quantity addQuantity: $addQuantity newQuantity: $newQuantity<br>\n" );
                    $sql = "Update inventory set Quantity = $newQuantity Where ID=$inventoryId";
                  } else {
                    $sql = "Insert Into inventory (ItemId, OwnerId, Quantity) values ($ItemId,$shooterId,$addQuantity)";
                  } 
                  echo ("$sql<br>\n" );
                  $r = query($sql);
               } 
             }  
             $sql = "Delete From inventory where OwnerId=$ID";
             $result = query ($sql);           
             echo ("Pipboy has just died" );
             $Message = "died";
             $cmd = "python sendMessage.py $IpAddress \"$Message\"";
             echo ( "<h1>CMD:</h1><br>$cmd<BR>\n");
             exec($cmd);	            
             killDevices ($MAC);
           }
        }   
     }  
   }
} else {
   echo ("Command not handled:$Command<br>$cmd<br>\n" );
}

$isTriggered = false;
if ($Pipboy) { // We found the MAC address in the database
  $sql = "UPDATE pipboys SET Timestamp=CURRENT_TIMESTAMP,IpAddress='$IpAddress',Message='$Message' WHERE MAC='$MAC'";
  $result = mysql_query($sql) or die("Could not execute: $sql");  
  echo ("<BR>Placed message:<br>$Message<br> in database");
} else { // This is a new player who has never been in the game before
   echo ("Could not find $MAC");
   echo ("<p>Inserting MAC: $MAC into lostpipboys table<br>\n");
   $result = query ( "Select * From lostpipboys Where MAC='$MAC'" );
   $result = mysql_fetch_assoc($result);   
   if ($result) {
      echo ("$MAC already in lostpipboys<br>\n");
   } else {
      $sql = "insert into lostpipboys (MAC) Values ('$MAC')";
      echo "$sql<br>\n";
      $result = query ($sql);    
   }
}

     
?>
</body>
</html>