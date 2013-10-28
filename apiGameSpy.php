<?php
/*
API GameSpy.
Récupere des informations depuis un serveur Arma 3 et renvoi le résultat
sous forme d'array.
*/


function getInfosFromPublic()
{
    return getInfosFromServer("ts3.armasites.com",2302,2302);
}

function getInfosFromPrive()
{
    return getInfosFromServer("ts3.armasites.com",2322,2322);
}


function getvalue($srv_value, $srv_data)
{
        // Retourne la valeur du parametre demandé
        $srv_value = array_search ($srv_value, $srv_data);

        if ($srv_value === false)
        {
                return false;
        } else {
                $srv_value = $srv_data[$srv_value+1];
                return $srv_value;
        };
};


function parse($p_info)
{
    $resultats = array();
    $resultats["joueurs"] = array();

    // Etape 1 : Déterminer le nombre de joueurs présents
    $nbPlayer = 0;
    $i = 2;

    while(!empty($p_info[$i]))
    {
       
        $nbPlayer++;
        $i++;

    }
    

    // Etape 2 : Remplir le tableau des joueurs
    $i = 0;
    $increment = $nbPlayer + 3;
    $index = 2;
    while ($i < $nbPlayer)
    {
        $arrayJoueur["pseudo"] = $p_info[$index+$i];
        $arrayJoueur["team"]  = $p_info[($index+(1*$increment))+$i];
        $arrayJoueur["score"]   = $p_info[($index+(2*$increment))+$i];
        $arrayJoueur["mort"]   = $p_info[($index+(3*$increment))+$i];
        array_push($resultats["joueurs"], $arrayJoueur);
        $i++;
    }

    return $resultats;
}



function getInfosFromServer($addr,$port,$queryport)
{
        global $imgxsize, $imgysize, $debugGS, $smallimage, $randomimage,$makeimage, $imgfolder, $font, $fontsize, $fontsizesmall, $fontsizebig, $maxwait;
        $sock = fsockopen(("udp://" . $addr), $queryport, $errno, $errdesc, $maxwait);
        $reply  = "";
        $query = pack("c*",0xFE,0xFD,0x09,0x04,0x05,0x06,0x07,0xFF,0xFF,0xFF);
        $hostname               = 'hostname';
        $gametype               = 'gamemode';
        $gamemode               = 'gametype';
        $islandname             = 'mapname';
        $version                = 'gamever';
        $mapname                = 'mission';
        $maxplayers             = 'maxplayers';
        $needpass               = 'password';
        $numplayers             = 'numplayers';
        $numteams               = 'numteams';
        $split                  = "\x00";
        $playloc                = "player_";
        $playoffset             = 0;
        $imgfolder              .= "arma/";
        $img                    = "arma_bg1.png";
        $challenge = "";
        $val = 0;
        
        if (!$sock) 
        {
             $reply = "";
        } 
        else 
        {
            fwrite($sock,$query);
            @socket_set_timeout($sock, 2);
            $reply = @fread($sock, 4096);
            
            $challenge = (substr ( $reply , 4));
           

            for ($x=1; $x < (strlen($challenge)); $x++)
            {
                    $char = $challenge[strlen($challenge)-$x-1];
                    if ($char == "-") 
                    {
                            $val = 0 - $val;
                    } else {
                            $val = $val + (intval($char) * max(1,pow(10, ($x - 1))));
                    }
            }

            if (strlen($reply) <= 0) 
            {
                   fclose($sock);
            }
        }

        if (!$sock) 
        {
                $reply = "";
        } else 
        {
                $ar = unpack("C*", pack("L", $challenge));
                $query  = pack("c*",0xFE,0xFD,0x00,0x04,0x05,0x07,0x08,($val >> 24),($val>> 16),($val >> 8),($val >> 0),0xFF,0xFF,0xFF,0x01);
                $challenge . " - " . $val . " -->";
                fwrite($sock,$query);
                @socket_set_timeout($sock, 2);
                $reply = @fread($sock, 4096);
                fclose($sock);
                
                if (strlen($reply) > 100) {
                        $querysuccess = true;
                } 
                else 
                {
                        $querysuccess = false;
                }
        }


    
        if ($querysuccess)
        {
            // Récuperation des données
            $g_end  = strpos($reply, $playloc) + $playoffset;
            $g_info = substr($reply, 5, $g_end - 5);
            $g_info = explode($split, $g_info);

            $p_end  = strlen($reply);
            $p_info = substr($reply, $g_end, $p_end);
            $p_info = explode($split, $p_info);

            $resultats = parse($p_info);

            $resultats['hostname']          = getvalue($hostname,   $g_info);
            $resultats['gametype']          = getvalue($gametype,   $g_info);
            $resultats['gamemode']          = getvalue($gamemode,   $g_info);
            $resultats['islandname']        = getvalue($islandname, $g_info);
            $resultats['version']           = getvalue($version,    $g_info);
            $resultats['mapname']           = getvalue($mapname,    $g_info);
            $resultats['maxplayers']        = getvalue($maxplayers, $g_info);
            $resultats['needpass']          = getvalue($needpass,   $g_info);
            $resultats['numplayers']        = getvalue($numplayers, $g_info);
            if($numteams <> '')
                    $resultats['numteams']  = getvalue($numteams,   $g_info);
            $resultats['gamename']          = $resultats['hostname'] . " (Ver: " . $resultats['version'] . ")";
            $resultats['uniqueid'] = str_replace(array("[","]",".","-","_","/","\\",":"),"",$addr . $port);

            return $resultats;
        }
        else
        {
            return 0;
        }
};





?>