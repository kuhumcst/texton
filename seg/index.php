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
ToolID         : seg
PassWord       : 
Version        : 1.0
Title          : TEIP5-segmenter
ServiceURL     : http://localhost/seg/	*** TODO make sure your web service listens on this address and that this script is readable for the webserver. ***
Publisher      : Københavns Universitet, Center for Sprogteknologi
ContentProvider: Københavns Universitet, Center for Sprogteknologi
Creator        : Bart Jongejan
InfoAbout      : http://cst.dk/online/TEIP5-segmenter/
Description    : Reads tokens and sentences as annotations and produces segment annotations, where segments refer to tokens, not to the base text.
ExternalURI    : 
XMLparms       : 
PostData       : 
Inactive       : on
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/seg.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */
                
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
                $tempfilename = tempFileName("seg_{$requestParm}_");
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

    function do_seg()
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
        $IfacetsentF = "";	/* Input with annotationstyper sentences */
        $IfacettokF = "";	/* Input with annotationstyper tokens */
        $Ifacetsent = false;	/* Annotationstyper in input is sentences if true */
        $Ifacettok = false;	/* Annotationstyper in input is tokens if true */
        $Iformattxtann = false;	/* Format in input is TEIP5DKCLARIN_ANNOTATION if true */
        $Ofacetseg = false;	/* Annotationstyper in output is segments if true */
        $Oformattxtann = false;	/* Format in output is TEIP5DKCLARIN_ANNOTATION if true */

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
        if( hasArgument("IfacetsentF") )
            {        
            $IfacetsentF = requestFile("IfacetsentF");
            if($IfacetsentF == '')
                {
                logit("HTTP/1.0 404 Input with annotationstyper 'sentences' not found (IfacetsentF parameter). ");
                header("HTTP/1.0 404 Input with annotationstyper 'sentences' not found (IfacetsentF parameter). ");
                return;
                }
            $echos = $echos . "IfacetsentF=$IfacetsentF ";
            }
        if( hasArgument("IfacettokF") )
            {        
            $IfacettokF = requestFile("IfacettokF");
            if($IfacettokF == '')
                {
                logit("HTTP/1.0 404 Input with annotationstyper 'tokens' not found (IfacettokF parameter). ");
                header("HTTP/1.0 404 Input with annotationstyper 'tokens' not found (IfacettokF parameter). ");
                return;
                }
            $echos = $echos . "IfacettokF=$IfacettokF ";
            }

/************************
* input/output features *
************************/
        if( hasArgument("Ifacet") )
            {
            $Ifacetsent = existsArgumentWithValue("Ifacet", "sent");
            $Ifacettok = existsArgumentWithValue("Ifacet", "tok");
            $echos = $echos . "Ifacetsent=$Ifacetsent " . "Ifacettok=$Ifacettok ";
            }
        if( hasArgument("Iformat") )
            {
            $Iformattxtann = existsArgumentWithValue("Iformat", "txtann");
            $echos = $echos . "Iformattxtann=$Iformattxtann ";
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacetseg = existsArgumentWithValue("Ofacet", "seg");
            $echos = $echos . "Ofacetseg=$Ofacetseg ";
            }
        if( hasArgument("Oformat") )
            {
            $Oformattxtann = existsArgumentWithValue("Oformat", "txtann");
            $echos = $echos . "Oformattxtann=$Oformattxtann ";
            }

/*******************************
* input/output features styles *
*******************************/

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $segfile = tempFileName("seg-results");
        $command = "echo $echos >> $segfile";
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

        $segfile = tempFileName("seg-results");

        ob_start();
        var_dump($_REQUEST);
        $dump = ob_get_clean();    
        logit($dump);

        $command = "../bin/bracmat \"get'\\\"seg.bra\\\"\" $IfacettokF $IfacetsentF $segfile"; // perhaps 2x faster
        logit($command);

        if(($cmd = popen($command, "r")) == NULL)
            {
            throw new SystemExit(); // instead of exit()
            }

        while($read = fgets($cmd))
            {
            }

        pclose($cmd);

// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $segfile
//*/
        $tmpf = fopen($segfile,'r');

        if($tmpf)
            {
  //          logit('output from seg:');
            while($line = fgets($tmpf))
                {
//                logit($line);
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
    do_seg();
    }
catch (SystemExit $e) 
    { 
    header("HTTP/1.0 404 An error occurred:" . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }

?>

