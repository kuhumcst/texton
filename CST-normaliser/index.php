<?php
header("Content-type:text/plain; charset=UTF-8");
/*
 * This PHP script is generated by CLARIN-DK's tool registration form 
 * (https://clarin.dk/tools/register). It should, with no or few adaptations
 * work out of the box as a dummy for your web service. The output returned
 * to the CLARIN-DK workflow manager is just a listing of the HTTP parameters
 * received by this web service from the CLARIN-DK workflow manager, and not
 * the output proper. For that you have to add your code to this script and
 * deactivate the dummy functionality. (The comments near the end of this
 * script explain how that is done.)
 *
 * Places in this script that require your attention are marked 'TODO'.
 */
/*
ToolID         : CST-Norm
PassWord       : 
Version        : 7.13.2017.1208
Title          : CST-Normaliser
ServiceURL     : http://localhost/CST-normaliser/	*** TODO make sure your web service listens on this address and that this script is readable for the webserver. ***
Publisher      : CST
ContentProvider: cst.ku.dk
Creator        : CST
InfoAbout      : cst.dk
Description    : Normalises older (1200-1900) Danish text to spelling rules as employed in ODS (Ordbog over det danske Sprog)
ExternalURI    : http://localhost/tools/
XMLparms       : 
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/CSTNorm.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */
                
/*  TODO Set $dodelete to false if temporary files in /tmp should not be deleted before returning. */
$dodelete = true;
$tobedeleted = array();


function loginit()  /* Wipes the contents of the log file! TODO Change this behaviour if needed. */
    {
    return;
    global $toollog,$ftemp;
    $ftemp = fopen($toollog,'w');
    if($ftemp)
        {
        fwrite($ftemp,$toollog . "\n");
        fclose($ftemp);
        }
    }
    
function logit($str) /* TODO You can use this function to write strings to the log file. */
    {
    return;
    global $toollog,$ftemp;
    $ftemp = fopen($toollog,'a');
    if($ftemp)
        {
        fwrite($ftemp,$str . "\n");
        fclose($ftemp);
        }
    }
    
class SystemExit extends Exception {}
try {
    function hasArgument ($parameterName)
        {
        return isset($_REQUEST["$parameterName"]);
        }

    function getArgument ($parameterName)
        {
        return isset($_REQUEST["$parameterName"]) ? $_REQUEST["$parameterName"] : "";
        }

    function existsArgumentWithValue ($parameterName, $parameterValue)
        {
        /* Check whether there is an argument <parameterName> that has value 
           <parameterValue>. 
           There may be any number of arguments with name <parameterName> !
        */
        $query  = explode('&', $_SERVER['QUERY_STRING']);

        foreach( $query as $param )
            {
            list($name, $value) = explode('=', $param);
            if($parameterName == urldecode($name) && $parameterValue == urldecode($value))
                return true;
            }
        return false;
        }

    function tempFileName($suff) /* TODO Use this to create temporary files, if needed. */
        {
        global $dodelete;
        global $tobedeleted;
        $tmpno = tempnam('/tmp', $suff);
        if($dodelete)
            $tobedeleted[$tmpno] = true;
        return $tmpno;
        }
        
    function requestFile($requestParm) // e.g. "IfacettokF"
        {
        logit("requestFile(" . $requestParm . ")");

        if(isset($_REQUEST[$requestParm]))
            {
            $urlbase = isset($_REQUEST["base"]) ? $_REQUEST["base"] : "http://localhost/toolsdata/";

            $item = $_REQUEST[$requestParm];
            $url = $urlbase . urlencode($item);
            logit("requestParm:$requestParm");
            logit("urlbase:$urlbase");
            logit("item:$item");
            logit("url[$url]");

            $handle = fopen($url, "r");
            if($handle == false)
                {
                logit("Cannot open url[$url]");
                return "";
                }
            else
                {
                $tempfilename = tempFileName("CSTNorm_{$requestParm}_");
                $temp_fh = fopen($tempfilename, 'w');
                if($temp_fh == false)
                    {
                    fclose($handle);
                    logit("handle closed. Cannot open $tempfilename");
                    return "";
                    }
                else
                    {
                    while (!feof($handle)) 
                        {
                        $read = fread($handle, 8192);
                        fwrite($temp_fh, $read);    
                        }
                    fclose($temp_fh);
                    fclose($handle);
                    return $tempfilename;
                    }
                }
            }
        logit("empty");
        return "";
        }    

    function do_CSTNorm()
        {
        global $dodelete;
        global $tobedeleted;
/***************
* declarations *
***************/

/*
 * TODO Use the variables defined below to configure your tool for the right 
 * input files and the right settings.
 * The input files are local files that your tool can open and close like any
 * other file.
 * If your tool needs to create temporary files, use the tempFileName() 
 * function. It can mark the temporary files for deletion when the webservice
 * is done. (See the global dodelete variable.)
 */
        $base = "";	/* URL from where this web service downloads input. The generated script takes care of that, so you can ignore this variable. */
        $job = "";	/* Only used if this web service returns 201 and POSTs result later. In that case the uploaded file must have the name of the job. */
        $post2 = "";	/* Only used if this web service returns 201 and POSTs result later. In that case the uploaded file must be posted to this URL. */
        $echos = "";	/* List arguments and their actual values. For sanity check of this generated script. All references to this variable can be removed once your web service is working as intended. */
        $F = "";	/* Input (ONLY used if there is exactly ONE input to this workflow step) */
        $IfacetsegF = "";	/* Input with type of content segments (Sætningssegmenter) */
        $IfacettokF = "";	/* Input with type of content tokens (Tokens) */
        $Iambigamb = false;	/* Ambiguity in input is ambiguous (tvetydig) if true */
        $Iambiguna = false;	/* Ambiguity in input is unambiguous (utvetydig) if true */
        $Iappunn = false;	/* Appearance in input is unnormalised (ikke-normaliseret) if true */
        $Ifacetseg = false;	/* Type of content in input is segments (Sætningssegmenter) if true */
        $Ifacettok = false;	/* Type of content in input is tokens (Tokens) if true */
        $Iformatflat = false;	/* Format in input is flat (flad) if true */
        $Ilangda = false;	/* Language in input is Danish (dansk) if true */
        $Iperiodc13 = false;	/* Spelling in input is 1200-1300 if true */
        $Iperiodc14 = false;	/* Spelling in input is 1300-1400 if true */
        $Iperiodc15 = false;	/* Spelling in input is 1400-1500 if true */
        $Iperiodc16 = false;	/* Spelling in input is 1500-1600 if true */
        $Iperiodc17 = false;	/* Spelling in input is 1600-1700 if true */
        $Iperiodc18 = false;	/* Spelling in input is 1700-1800 if true */
        $Iperiodc19 = false;	/* Spelling in input is 1800-1900 if true */
        $Ipresnml = false;	/* Presentation in input is normal if true */
        $Oambigamb = false;	/* Ambiguity in output is ambiguous (tvetydig) if true */
        $Oambiguna = false;	/* Ambiguity in output is unambiguous (utvetydig) if true */
        $Oappnrm = false;	/* Appearance in output is normalised (normaliseret) if true */
        $Ofacetseg = false;	/* Type of content in output is segments (Sætningssegmenter) if true */
        $Ofacettok = false;	/* Type of content in output is tokens (Tokens) if true */
        $Oformatflat = false;	/* Format in output is flat (flad) if true */
        $Olangda = false;	/* Language in output is Danish (dansk) if true */
        $Operiodc13 = false;	/* Spelling in output is 1200-1300 if true */
        $Operiodc14 = false;	/* Spelling in output is 1300-1400 if true */
        $Operiodc15 = false;	/* Spelling in output is 1400-1500 if true */
        $Operiodc16 = false;	/* Spelling in output is 1500-1600 if true */
        $Operiodc17 = false;	/* Spelling in output is 1600-1700 if true */
        $Operiodc18 = false;	/* Spelling in output is 1700-1800 if true */
        $Operiodc19 = false;	/* Spelling in output is 1800-1900 if true */
        $Opresalf = false;	/* Presentation in output is alphabetic list (alfabetisk liste) if true */
        $Opresfrq = false;	/* Presentation in output is frequency list (frekvensliste) if true */
        $Opresnml = false;	/* Presentation in output is normal if true */

        if( hasArgument("base") )
            {
            $base = getArgument("base");
            }
        if( hasArgument("job") )
            {
            $job = getArgument("job");
            }
        if( hasArgument("post2") )
            {
            $post2 = getArgument("post2");
            }
        $echos = "base=$base job=$job post2=$post2 ";

/*********
* input  *
*********/
        if( hasArgument("F") )
            {        
            $F = requestFile("F");
            if($F == '')
                {
                header("HTTP/1.0 404 Input not found (F parameter). ");
                return;
                }
            $echos = $echos . "F=$F ";
            }
        if( hasArgument("IfacetsegF") )
            {        
            $IfacetsegF = requestFile("IfacetsegF");
            if($IfacetsegF == '')
                {
                header("HTTP/1.0 404 Input with type of content 'segments (Sætningssegmenter)' not found (IfacetsegF parameter). ");
                return;
                }
            $echos = $echos . "IfacetsegF=$IfacetsegF ";
            }
        if( hasArgument("IfacettokF") )
            {        
            $IfacettokF = requestFile("IfacettokF");
            if($IfacettokF == '')
                {
                header("HTTP/1.0 404 Input with type of content 'tokens (Tokens)' not found (IfacettokF parameter). ");
                return;
                }
            $echos = $echos . "IfacettokF=$IfacettokF ";
            }

/************************
* input/output features *
************************/
        if( hasArgument("Iambig") )
            {
            $Iambigamb = existsArgumentWithValue("Iambig", "amb");
            $Iambiguna = existsArgumentWithValue("Iambig", "una");
            $echos = $echos . "Iambigamb=$Iambigamb " . "Iambiguna=$Iambiguna ";
            }
        if( hasArgument("Iapp") )
            {
            $Iappunn = existsArgumentWithValue("Iapp", "unn");
            $echos = $echos . "Iappunn=$Iappunn ";
            }
        if( hasArgument("Ifacet") )
            {
            $Ifacetseg = existsArgumentWithValue("Ifacet", "seg");
            $Ifacettok = existsArgumentWithValue("Ifacet", "tok");
            $echos = $echos . "Ifacetseg=$Ifacetseg " . "Ifacettok=$Ifacettok ";
            }
        if( hasArgument("Iformat") )
            {
            $Iformatflat = existsArgumentWithValue("Iformat", "flat");
            $echos = $echos . "Iformatflat=$Iformatflat ";
            }
        if( hasArgument("Ilang") )
            {
            $Ilangda = existsArgumentWithValue("Ilang", "da");
            $echos = $echos . "Ilangda=$Ilangda ";
            }
        if( hasArgument("Iperiod") )
            {
            $Iperiodc13 = existsArgumentWithValue("Iperiod", "c13");
            $Iperiodc14 = existsArgumentWithValue("Iperiod", "c14");
            $Iperiodc15 = existsArgumentWithValue("Iperiod", "c15");
            $Iperiodc16 = existsArgumentWithValue("Iperiod", "c16");
            $Iperiodc17 = existsArgumentWithValue("Iperiod", "c17");
            $Iperiodc18 = existsArgumentWithValue("Iperiod", "c18");
            $Iperiodc19 = existsArgumentWithValue("Iperiod", "c19");
            $echos = $echos . "Iperiodc13=$Iperiodc13 " . "Iperiodc14=$Iperiodc14 " . "Iperiodc15=$Iperiodc15 " . "Iperiodc16=$Iperiodc16 " . "Iperiodc17=$Iperiodc17 " . "Iperiodc18=$Iperiodc18 " . "Iperiodc19=$Iperiodc19 ";
            }
        if( hasArgument("Ipres") )
            {
            $Ipresnml = existsArgumentWithValue("Ipres", "nml");
            $echos = $echos . "Ipresnml=$Ipresnml ";
            }
        if( hasArgument("Oambig") )
            {
            $Oambigamb = existsArgumentWithValue("Oambig", "amb");
            $Oambiguna = existsArgumentWithValue("Oambig", "una");
            $echos = $echos . "Oambigamb=$Oambigamb " . "Oambiguna=$Oambiguna ";
            }
        if( hasArgument("Oapp") )
            {
            $Oappnrm = existsArgumentWithValue("Oapp", "nrm");
            $echos = $echos . "Oappnrm=$Oappnrm ";
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacetseg = existsArgumentWithValue("Ofacet", "seg");
            $Ofacettok = existsArgumentWithValue("Ofacet", "tok");
            $echos = $echos . "Ofacetseg=$Ofacetseg " . "Ofacettok=$Ofacettok ";
            }
        if( hasArgument("Oformat") )
            {
            $Oformatflat = existsArgumentWithValue("Oformat", "flat");
            $echos = $echos . "Oformatflat=$Oformatflat ";
            }
        if( hasArgument("Olang") )
            {
            $Olangda = existsArgumentWithValue("Olang", "da");
            $echos = $echos . "Olangda=$Olangda ";
            }
        if( hasArgument("Operiod") )
            {
            $Operiodc13 = existsArgumentWithValue("Operiod", "c13");
            $Operiodc14 = existsArgumentWithValue("Operiod", "c14");
            $Operiodc15 = existsArgumentWithValue("Operiod", "c15");
            $Operiodc16 = existsArgumentWithValue("Operiod", "c16");
            $Operiodc17 = existsArgumentWithValue("Operiod", "c17");
            $Operiodc18 = existsArgumentWithValue("Operiod", "c18");
            $Operiodc19 = existsArgumentWithValue("Operiod", "c19");
            $echos = $echos . "Operiodc13=$Operiodc13 " . "Operiodc14=$Operiodc14 " . "Operiodc15=$Operiodc15 " . "Operiodc16=$Operiodc16 " . "Operiodc17=$Operiodc17 " . "Operiodc18=$Operiodc18 " . "Operiodc19=$Operiodc19 ";
            }
        if( hasArgument("Opres") )
            {
            $Opresalf = existsArgumentWithValue("Opres", "alf");
            $Opresfrq = existsArgumentWithValue("Opres", "frq");
            $Opresnml = existsArgumentWithValue("Opres", "nml");
            $echos = $echos . "Opresalf=$Opresalf " . "Opresfrq=$Opresfrq " . "Opresnml=$Opresnml ";
            }

/*******************************
* input/output features styles *
*******************************/

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $CSTNormfile = tempFileName("CSTNorm-results");
        $command = "echo $echos >> $CSTNormfile";
        logit($command);

        if(($cmd = popen($command, "r")) == NULL)
            {
            throw new SystemExit(); // instead of exit()
            }

        while($read = fgets($cmd))
            {
            }

        pclose($cmd);
/*/
// YOUR CODE STARTS HERE.
//
        $CSTNormfile = tempFileName("CSTNorm-results");
        $toolres = '../texton-linguistic-resources/';
        $foptarg = "notags";
        $toptarg = "-";
        
        $dict = "/dict";
        $flexrules = "/flexrules";
        $periodsubdir = "";
        $flexrulessubdir = "/0";
        $cstlemma = "../bin/cstlemma";

        if($Ilangda)
            {
            $language = "da";
            if($Iperiodc19)
                {
                $periodsubdir = "/c1920n";
                $flexrulessubdir = "/1";
                }
	    else
                {
                $periodsubdir = "/c13-c18norm";
                $flexrulessubdir = "/1";
                }
            }
        if(hasArgument("F"))
            {
	    $fil = $F;
	    }
	else
	    {
	    $fil = $IfacettokF;
	    }

        if($Oambiguna)
            {
            $Uu = "-U -u";
            }
        else
            $Uu = "-U- -u-";

        if($Opresalf)
            $command = "$cstlemma -L -s' ;; ' -I'\$w\\s' -eU -p -qwft -t$toptarg $Uu -H0 -B'\$f \$w (\$W)\\n' -b'\$f \$w (\$W)\\n' -W'\$f \$w' -l- -f'$toolres$language/lemmatiser/$foptarg$periodsubdir$flexrulessubdir$flexrules' -d'$toolres$language/lemmatiser/$foptarg$periodsubdir$dict' -i $fil -o $CSTNormfile";
        else if($Opresfrq) 
            $command = "$cstlemma -L -s' ;; ' -I'\$w\\s' -eU -p -qfwt -t$toptarg $Uu -H0 -B'\$f \$w (\$W)\\n' -b'\$f \$w (\$W)\\n' -W'\$f \$w' -l- -f'$toolres$language/lemmatiser/$foptarg$periodsubdir$flexrulessubdir$flexrules' -d'$toolres$language/lemmatiser/$foptarg$periodsubdir$dict' -i $fil -o $CSTNormfile";
        else
            $command = "$cstlemma -L -s' ;; ' -I'\$w\\s' -eU -p -q-   -t$toptarg $Uu -H0 -B'\$w'              -b'\$w'   -c'\$b[[\$b0]?\$B]\$s' -l- -f'$toolres$language/lemmatiser/$foptarg$periodsubdir$flexrulessubdir$flexrules' -d'$toolres$language/lemmatiser/$foptarg$periodsubdir$dict' -i $fil -o $CSTNormfile";

        logit($command);


        if(($cmd = popen($command, "r")) == NULL)
            {
            throw new SystemExit(); // instead of exit()
            }

        while($read = fgets($cmd))
            {
            }

        pclose($cmd);
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $CSTNormfile
//*/
        $tmpf = fopen($CSTNormfile,'r');

        if($tmpf)
            {
            logit('output from CSTNorm:');
            while($line = fgets($tmpf))
                {
                logit($line);
                print $line;
                }
            fclose($tmpf);
            }

        if($dodelete)
            {
            foreach ($tobedeleted as $filename => $dot) 
                {
                if($dot)
                    unlink($filename);
                }
            unset($tobedeleted);
            }
        }
    loginit();
    do_CSTNorm();
    }
catch (SystemExit $e) 
    { 
    header("HTTP/1.0 404 An error occurred:" . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }

?>

