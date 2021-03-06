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
ToolID         : teianno2orgmode
PassWord       : 
Version        : 1
Title          : TEI P5 anno to Org-mode
ServiceURL     : http://localhost/tei2org	*** TODO make sure your web service listens on this address and that this script is readable for the webserver. ***
Publisher      : CST
ContentProvider: CST
Creator        : Bart Jongejan
InfoAbout      : -
Description    : Convert TEI P5 stand-off annotation to a two column file in Org-mode format. The first column contains a token, the second contains the annotation: POS-tag, word class, or lemma
ExternalURI    : 
XMLparms       : 
PostData       : 
Inactive       : on
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/teianno2orgmode.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */
                
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
                $tempfilename = tempFileName("teianno2orgmode_{$requestParm}_");
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

    function do_teianno2orgmode()
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
        $IfacetlemF = "";	/* Input with type of content lemmas (Lemma) */
        $IfacetposF = "";	/* Input with type of content PoS-tags (PoS-tags) */
        $IfacettokF = "";	/* Input with type of content tokens (Tokens) */
        $Ifacetlem = false;	/* Type of content in input is lemmas (Lemma) if true */
        $Ifacetpos = false;	/* Type of content in input is PoS-tags (PoS-tags) if true */
        $Ifacettok = false;	/* Type of content in input is tokens (Tokens) if true */
        $Iformattxtann = false;	/* Format in input is TEIP5DKCLARIN_ANNOTATION if true */
        $Ofacettl = false;	/* Type of content in output is tokens,lemmas (tokens,lemmaer) if true */
        $Ofacettp = false;	/* Type of content in output is tokens,PoS-tags (tokens,PoS-tags) if true */
        $Oformatdipl = false;	/* Format in output is Org-mode if true */

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
        if( hasArgument("IfacetlemF") )
            {        
            $IfacetlemF = requestFile("IfacetlemF");
            if($IfacetlemF == '')
                {
                header("HTTP/1.0 404 Input with type of content 'lemmas (Lemma)' not found (IfacetlemF parameter). ");
                return;
                }
            $echos = $echos . "IfacetlemF=$IfacetlemF ";
            }
        if( hasArgument("IfacetposF") )
            {        
            $IfacetposF = requestFile("IfacetposF");
            if($IfacetposF == '')
                {
                header("HTTP/1.0 404 Input with type of content 'PoS-tags (PoS-tags)' not found (IfacetposF parameter). ");
                return;
                }
            $echos = $echos . "IfacetposF=$IfacetposF ";
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
        if( hasArgument("Ifacet") )
            {
            $Ifacetlem = existsArgumentWithValue("Ifacet", "lem");
            $Ifacetpos = existsArgumentWithValue("Ifacet", "pos");
            $Ifacettok = existsArgumentWithValue("Ifacet", "tok");
            $echos = $echos . "Ifacetlem=$Ifacetlem " . "Ifacetpos=$Ifacetpos " . "Ifacettok=$Ifacettok ";
            }
        if( hasArgument("Iformat") )
            {
            $Iformattxtann = existsArgumentWithValue("Iformat", "txtann");
            $echos = $echos . "Iformattxtann=$Iformattxtann ";
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacettl = existsArgumentWithValue("Ofacet", "tl");
            $Ofacettp = existsArgumentWithValue("Ofacet", "tp");
            $echos = $echos . "Ofacettl=$Ofacettl " . "Ofacettp=$Ofacettp ";
            }
        if( hasArgument("Oformat") )
            {
            $Oformatdipl = existsArgumentWithValue("Oformat", "dipl");
            $echos = $echos . "Oformatdipl=$Oformatdipl ";
            }

/*******************************
* input/output features styles *
*******************************/

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $teianno2orgmodefile = tempFileName("teianno2orgmode-results");
        $command = "echo $echos >> $teianno2orgmodefile";
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
//        TODO your code!
        $command = "echo $echos >> $teianno2orgmodefile";
        logit($command);

        logit("diplout($Ifacettok,$Ifacetseg,$Ifacetpos,$Ifacetlem)");

        $teianno2orgmodefile = tempFileName("conllout-results");
        if($Ifacetpos)        
            $command = "../bin/bracmat 'get\$\"tei2org.bra\"' '$teianno2orgmodefile' '$IfacettokF' '$IfacetposF' 'Part of Speech'";
        else /* $Ifacetlem */
            $command = "../bin/bracmat 'get\$\"tei2org.bra\"' '$teianno2orgmodefile' '$IfacettokF' '$IfacetlemF' 'lemma'";

        logit($command);

        if(($cmd = popen($command, "r")) == NULL)
            {
            throw new SystemExit(); // instead of exit()
            }

        while($read = fgets($cmd))
            {
            }

        pclose($cmd);
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $teianno2orgmodefile
//*/
        $tmpf = fopen($teianno2orgmodefile,'r');

        if($tmpf)
            {
            //logit('output from teianno2orgmode:');
            while($line = fgets($tmpf))
                {
              //  logit($line);
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
    do_teianno2orgmode();
    }
catch (SystemExit $e) 
    { 
    header("HTTP/1.0 404 An error occurred:" . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }

?>


