<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
		<script type="text/javascript">
			function enableElements()
			{
			document.getElementById("selectmore").disabled=false;
			document.getElementById("reroll").disabled=false;
			document.getElementById("keep").disabled=false;
			}
		</script>
    </head>
    <body>
		<form action="index.php" method="POST"> 
			
		<?php
		$vals = array('$', 'G','R','E1','E2','D');
		$tally = array('$'=>0, 'G'=>0,'R'=>0,'E1'=>0,'E2'=>0,'D'=>0);
		$key = md5('ooo fancy encryption key, soooo secure');
		$gamedat = $_REQUEST['gamedat']; 
		var_dump("raw gamedat: ".$gamedat);	
		if ($gamedat != "")
		{	
			//echo "gamedat exists <br />";
			$base_64_decoded_data=base64_decode($gamedat);
			var_dump("base 64 decoded: ".$base_64_decoded_data);	
			$decrypted_data_json = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key,$base_64_decoded_data, MCRYPT_MODE_ECB), "\0");
			// Turn it back into an array
			var_dump("decrypted data json: ".$decrypted_data_json);
			$decrypted_data = json_decode($decrypted_data_json, true);
			$gamedat=$decrypted_data;
			var_dump('decrypted game data: ', $gamedat);		
			echo "turn: ".$gamedat['player_map'][$gamedat['turn']]."<br />";
			echo "Your score at the start of this turn:".$gamedat['players'][$gamedat['player_map'][$gamedat['turn']]]."<br />";
			echo $gamedat['player_map'][($gamedat['turn'] + 1) % count($gamedat['player_map'])]."'s score:".$gamedat['players'][$gamedat['player_map'][($gamedat['turn'] + 1) % count($gamedat['player_map'])]]."<br />";
			if ($_POST['name']!="")
			{
				echo "names just entered <br />";
				$gamedat['players'][$_POST['name']]=0;
				$gamedat['players'][$_POST['oppname']]=0;
				$gamedat['player_map'][0] = $_POST['name'];
				$gamedat['player_map'][1] = $_POST['oppname'];
				$gamedat['turn'] = 0;
				
				echo "turn=".$gamedat['player_map'][$gamedat['turn']]."<br />";

				echo "score=".$gamedat['players'][$gamedat['player_map'][$gamedat['turn']]]."<br />";
			}			
		}
		else{
			//echo "gamedat doesn't exist <br /><br />";
		
			echo "What's your name?";
			echo "<input type=\"text\" name=\"name\"/><br />";
			echo "What's your opponent's name?";
			echo "<input type=\"text\" name=\"oppname\"/><br /><br />";	

			$gamedat['players'][0] = "player1";
			$gamedat['players'][1] = "player2";
			$gamedat['player_map'][0] = $gamedat['players'][0];
			$gamedat['player_map'][1] = $gamedat['players'][1];
			$gamedat['turn'] = 0;
		}
		
		
		function roll(&$gamedat, $vals)
		{
			echo "rolling <br/>";		
			for ($i=0;$i<=5;$i++)
			{
				$x=rand(0,5);
				$gamedat[hand][$i] = $x;
				echo $vals[$gamedat[hand][$i]];
				echo "<input type=\"hidden\" name=\"".$i."\" value=\"".$vals[$gamedat[hand][$i]]."\">\n";
				$gamedat['dice'][$vals[$gamedat[hand][$i]]]++;
				echo "(\$gamedat['dice'][".$vals[$gamedat[hand][$i]]."]=".$gamedat['dice'][$vals[$gamedat[hand][$i]]].")<br />\n";
			}
		}
		
		
		function choices($gamedat, $vals, $key)
		{
			echo "calculating choices <br/>";
			$scored=false;
			//var_dump($gamedat);
			if ($gamedat['dice'][$vals[0]] >= 3 ) //$$$
			{
				$scored=true;
				echo "<br /><input type=\"radio\" name=\"choice\" value=\"$$$\" onclick=\"enableElements()\">three ".$vals[0]."s\n";
			}

			if ($gamedat['dice'][$vals[1]] >= 1 ) //G
			{
				$scored=true;
				echo "<br /><input type=\"radio\" name=\"choice\" value=\"G\" onclick=\"enableElements()\">one ".$vals[1]."\n";
			}

			if ($gamedat['dice'][$vals[5]] >= 1 ) //D
			{
				$scored=true;
				echo "<br /><input type=\"radio\" name=\"choice\" value=\"D\" onclick=\"enableElements()\">one ".$vals[5]."\n";
			}

			if ($gamedat['dice'][$vals[2]] >= 3 ) //RRR
			{
				$scored=true;
				echo "<br /><input type=\"radio\" name=\"choice\" value=\"RRR\" onclick=\"enableElements()\">three ".$vals[2]."s\n";
			}

			if ($gamedat['dice'][$vals[3]] >= 3 ) //E1E1E1
			{
				$scored=true;
				echo "<br /><input type=\"radio\" name=\"choice\" value=\"E1E1E1\" onclick=\"enableElements()\">three ".$vals[3]."s\n";
			}

			if ($gamedat['dice'][$vals[4]] >= 3 ) //E2E2E2
			{
				$scored=true;
				echo "<br /><input type=\"radio\" name=\"choice\" value=\"E2E2E2\" onclick=\"enableElements()\">three ".$vals[4]."s\n";
			}

			if ($gamedat['dice'][$vals[5]] >= 4) //DDDD
			{
				$scored=true;
				echo "<br /><input type=\"radio\" name=\"choice\" value=\"DDDD\" onclick=\"enableElements()\">four ".$vals[5]."s\n";
			}
			if ($scored==false)
			{
				$gamedat[score]=0;
				finish($gamedat, $key);
			}
		}
		
		function encrypt(&$gamedat, $key)
		{
			//var_dump('Our game data:', $gamedat);
			// Turn this data into a string so we can encrypt it
			$data_json = json_encode($gamedat);
			//var_dump("Our string data: $data_json");
			$encrypted_data_json = mcrypt_encrypt(MCRYPT_RIJNDAEL_256,
							$key, $data_json, MCRYPT_MODE_ECB);
			//var_dump("Encrypted game: $encrypted_data_json");
			$base_64_encoded_data = base64_encode($encrypted_data_json);
			echo "<input type=\"hidden\" name=\"gamedat\" value=\"".$base_64_encoded_data."\">\n";
		}
		
		function calckept(&$gamedat, $choice, $vals)
		{
			echo "calculating score and removing scored dice<br />";
			echo "Testing: choice:".$choice."<br />"; 			
			//var_dump($gamedat);
			if ($choice == 'G' && $gamedat['dice']['G']>=1)
			{
				echo "used a G<br />" ;
				$gamedat['score']+=50;
				$gamedat['dice'][G]--;
				$count=0;
				for ($i=0;$i<=5;$i++)
				{
					if ($gamedat[hand][$i]==1 && $count==0)
					{
						$gamedat[hand][$i]="";
						$gamedat[used][$i]=1;
						$count++;
					}
				}
			}

			elseif ($choice == 'D' &&  $gamedat['dice']['D']>=1)
			{
				echo "used a D<br />" ;
				$gamedat['score']+=100;
				$gamedat['dice'][D]--;
				$count=0;
				for ($i=0;$i<=5;$i++)
				{
					if ($gamedat[hand][$i]==5 && $count==0)
					{
						$gamedat[hand][$i]="";
						$gamedat[used][$i]=5;
						$count++;
					}
				}
			}

			elseif ($choice == '$$$' &&  $gamedat['dice']['$']>=3)
			{
				echo "used a $$$<br />" ;
				$gamedat['score']+=500;
				$gamedat['dice']['$']-=3;
				$count=0;
				for ($i=0;$i<=5;$i++)
				{
					if ($gamedat[hand][$i]==0 && $count<=2)
					{
						$gamedat[hand][$i]="";
						$gamedat[used][$i]=0;
						$count++;
					}
				}
			}

			elseif ($choice == 'RRR' && $gamedat['dice']['R']>=3)
			{
				echo "used a RRR<br />";
				$gamedat['score']+=300;
				$gamedat['dice']['R']-=3;
				$count=0;
				for ($i=0;$i<=5;$i++)
				{
					if ($gamedat[hand][$i]==2 && $count<=2)
					{
						$gamedat[hand][$i]="";
						$gamedat[used][$i]=2;
						$count++;
					}
				}
			}

			elseif ($choice == 'E1E1E1' && $gamedat['dice']['E1']>=3)
			{
				echo "used a E1E1E1<br />";
				$gamedat['score']+=300;
				$gamedat['dice']['E1']-=3;
				$count=0;
				for ($i=0;$i<=5;$i++)
				{
					if ($gamedat[hand][$i]==3 && $count<=2)
					{
						$gamedat[hand][$i]="";
						$gamedat[used][$i]=3;
						$count++;
					}
				}
			}

			elseif ($choice == 'E2E2E2' && $gamedat['dice']['E2']>=3)
			{
				echo "used a E2E2E2<br />";
				$gamedat['score']+=300;
				$gamedat['dice']['E2']-=3;
				$count=0;
				for ($i=0;$i<=5;$i++)
				{
					if ($gamedat[hand][$i]==4 && $count<=2)
					{
						$gamedat[hand][$i]="";
						$gamedat[used][$i]=4;
						$count++;
					}
				}
			}

			
			elseif ($choice == 'DDDD' && $gamedat['dice']['D']>=3)
			{
				echo "used a DDDD<br />";
				$gamedat['score']+=300;
				$gamedat['dice']['D']-=4;
				$count=0;
				for ($i=0;$i<=5;$i++)
				{
					if ($gamedat[hand][$i]==5 && $count<=3)
					{
						$gamedat[hand][$i]="";
						$gamedat[used][$i]=5;
						$count++;
					}
				}
			}
			//var_dump($gamedat);
			
		}
		
		function display(&$gamedat, $vals)
		{
			echo "displaying remaining <br/>";
			for ($i=0;$i<=5;$i++)
			{
				if ($vals[$gamedat[hand][$i]]=="")
				{
					echo "unavailable: ".$vals[$gamedat[used][$i]]."<br />";	
				}
				else
				{
					echo $vals[$gamedat[hand][$i]];
					echo "<input type=\"hidden\" name=\"".$i."\" value=\"".$vals[$gamedat[hand][$i]]."\">\n";
					//$gamedat['dice'][$vals[$gamedat[hand][$i]]]++;
					echo "(\$gamedat['dice'][".$vals[$gamedat[hand][$i]]."]=".$gamedat['dice'][$vals[$gamedat[hand][$i]]].")<br />\n";	
				}

			}
		}
		
		function reroll(&$gamedat, $vals)
		{
			echo "rerolling remaining <br/>";
			foreach ($gamedat['dice'] as &$value)
			{
				$value=0;
			}	
			for ($i=0;$i<=5;$i++)
			{
				if ($vals[$gamedat[hand][$i]]=="")
				{
					echo "unavailable: ".$vals[$gamedat[used][$i]]."<br />";	
				}
				else
				{
					$x=rand(0,5);
					$gamedat[hand][$i] = $x;
					echo $vals[$gamedat[hand][$i]];
					echo "<input type=\"hidden\" name=\"".$i."\" value=\"".$vals[$gamedat[hand][$i]]."\">\n";
					$gamedat['dice'][$vals[$gamedat[hand][$i]]]++;
					echo "(\$gamedat['dice'][".$vals[$gamedat[hand][$i]]."]=".$gamedat['dice'][$vals[$gamedat[hand][$i]]].")<br />\n";
				}

			}
		}
		
		function finish(&$gamedat, $key)
		{
			
			
			$gamedat['hand'] ="";
			$gamedat['used'] ="";
			
						for ($i=0;$i<=5;$i++)
			{

		
				$x=rand(0,5);
				$gamedat[hand][$i] = $x;
				//echo $vals[$gamedat[hand][$i]];
				//echo "<input type=\"hidden\" name=\"".$i."\" value=\"".$vals[$gamedat[hand][$i]]."\">\n";
				$gamedat['dice'][$vals[$gamedat[hand][$i]]]++;
				//echo "(\$gamedat['dice'][".$vals[$gamedat[hand][$i]]."]=".$gamedat['dice'][$vals[$gamedat[hand][$i]]].")<br />\n";
				

			}
			
			//breaks on a failed first roll
			$gamedat['players'][$gamedat['player_map'][$gamedat['turn']]]+=$gamedat['score'];
			
			$gamedat['score']=0;
			$gamedat['turn'] = ($gamedat['turn'] + 1) % count($gamedat['player_map']);
			$data_json = json_encode($gamedat);
			var_dump("json encoded: ".$data_json);
			$encrypted_data_json = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data_json, MCRYPT_MODE_ECB);
			var_dump("encrypted json: ".$encrypted_data_json);
	
			$base_64_encoded_data = base64_encode($encrypted_data_json);
			var_dump("base 64 encoded: ".$base_64_encoded_data);
			$urlencoded_data = rawurlencode($base_64_encoded_data);
			var_dump("urlencoded: ".$urlencoded_data);
			//echo "?gamedat=".$urlencoded_data."<br />";
			
			//turn back into gamedat for testing..
			$gamedat=$base_64_encoded_data;
			
			$base_64_decoded_data=base64_decode($gamedat);
			var_dump("base 64 decoded: ".$base_64_decoded_data);	
			$decrypted_data_json = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key,$base_64_decoded_data, MCRYPT_MODE_ECB), "\0");
			var_dump("decrypted data json: ".$decrypted_data_json);
			$decrypted_data = json_decode($decrypted_data_json, true);
			$gamedat=$decrypted_data;
			var_dump('decrypted game data: ', $gamedat);
			$addy= $gamedat['player_map'][$gamedat['turn']];
			echo $addy;
			mail($addy, 'your turn',  "http://www.baconheist.com/greed/index.php?gamedat=".$urlencoded_data);
			
			echo "<a href=\"http://greed.localhost/index.php?gamedat=".$urlencoded_data."\">testing link</a><br />";
			
			
		}
	
			
		
		if(isset($_POST['selectmore']))
		{
			echo "select more<br />";
			calckept($gamedat, $_POST['choice'], $vals);
			display($gamedat, $vals);
			choices($gamedat, $vals, $key);
			encrypt($gamedat, $key);
			
			echo "<input type=\"hidden\" name=\"score\" value=\"".$score."\">\n";	
			echo "<br />score: ".$gamedat['score']."<br />";
		}
		
		elseif(isset($_POST['reroll']))
		{
			echo "reroll<br />";
			calckept($gamedat, $_POST['choice'], $vals);
			reroll($gamedat, $vals);
			choices($gamedat, $vals, $key);
			encrypt($gamedat, $key);
			
			echo "<input type=\"hidden\" name=\"score\" value=\"".$score."\">\n";	
			echo "<br />score: ".$gamedat['score']."<br />";
			
		}
		
		
		elseif(isset($_POST['keep']))
		{
			calckept($gamedat, $_POST['choice'], $vals);
			echo "saving ".$gamedat[score];	
			finish($gamedat, $key);
			
		}
		
		else 
		{

			if (isset($gamedat['dice']))
			{
				display($gamedat, $vals);	
			}
			else
			{
				roll($gamedat, $vals);
			}
			choices($gamedat, $vals, $key);
			encrypt($gamedat, $key);
		}
			
		
		?>
			<br />
			<input type="submit" id="selectmore" name="selectmore" value="select more" disabled="true"><br />
			<input type="submit" id="reroll" name="reroll" value="re-roll" disabled="true"><br /> <br />
			<input type="submit" id="keep" name="keep" value="select and save" disabled="true">
			
		</form>

	</body>
</html>
