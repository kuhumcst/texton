<?php
header("Content-type:text/plain; charset=UTF-8");
/*
 * This PHP script is generated by CLARIN-DK's tool registration form 
 * (http://localhost/texton/register). It should, with no or few adaptations
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
ToolID         : dapipe
PassWord       : 
Version        : 1.0
Title          : dapipe
Path in URL    : dapipe/	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : ITU
ContentProvider: ITU
Creator        : ITU
InfoAbout      : https://github.com/ITUnlp/dapipe
Description    : UDPipe tools for Danish. udpipe does pos-tagging, lemmatization and syntactic analysis. The syntactic analysis and lemmatization is always based on UDPipe's own pos-tagging. Using dapipe with TEI P5 input is discouraged, unless tokenisation and sentence extraction is done in separate steps, and not by dapipe itself.
ExternalURI    : 
XMLparms       : 
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/dapipe.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */
                
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
        logit("requestFile({$requestParm})");

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
                $tempfilename = tempFileName("dapipe_{$requestParm}_");
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

    function dapipe($filename)
        {
        chdir('dapipe');
        $command = './dapipe ' . $filename;

        logit("command: $command");

        $tmpo = tempFileName("DAPIPE");

        logit("$tmpo");

        $fpo = fopen($tmpo,"w");
        if(!$fpo)
            exit(1);

        if(($cmd = popen($command, "r")) == NULL)
            exit(1);

        while($read = fgets($cmd))
            {
            fwrite($fpo, $read);
            }

        fclose($fpo);

        pclose($cmd);
        return $tmpo;
        }

    function do_dapipe()
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
        $Iambiguna = false;	/* Ambiguity in input is unambiguous (utvetydig) if true */
        $Iappnrm = false;	/* Appearance in input is normalised (normaliseret) if true */
        $Ifacetseg = false;	/* Type of content in input is segments (Sætningssegmenter) if true */
        $Ifacettok = false;	/* Type of content in input is tokens (Tokens) if true */
        $Ifacettxt = false;	/* Type of content in input is text (Ingen annotation) if true */
        $Iformatflat = false;	/* Format in input is plain (flad) if true */
        $Iformatteip5 = false;	/* Format in input is TEIP5 if true */
        $Iformattxtann = false;	/* Format in input is TEIP5DKCLARIN_ANNOTATION if true */
        $Ilangda = false;	/* Language in input is Danish (dansk) if true */
        $Iperiodc21 = false;	/* Historical period in input is contemporary (efterkrigstiden) if true */
        $Ipresnml = false;	/* Assemblage in input is normal if true */
        $Oambiguna = false;	/* Ambiguity in output is unambiguous (utvetydig) if true */
        $Oappnrm = false;	/* Appearance in output is normalised (normaliseret) if true */
        $Ofacetlem = false;	/* Type of content in output is lemmas (Lemma) if true */
        $Ofacetmrf = false;	/* Type of content in output is morphological features (morfologiske træk) if true */
        $Ofacetpos = false;	/* Type of content in output is PoS-tags (PoS-tags) if true */
        $Ofacetseg = false;	/* Type of content in output is segments (Sætningssegmenter) if true */
        $Ofacetstx = false;	/* Type of content in output is syntax (dependency structure) (Syntaks (dependensstruktur)) if true */
        $Ofacettok = false;	/* Type of content in output is tokens (Tokens) if true */
        $Oformatconll = false;	/* Format in output is CoNLL if true */
        $Oformatteip5 = false;	/* Format in output is TEIP5 if true */
        $Oformattxtann = false;	/* Format in output is TEIP5DKCLARIN_ANNOTATION if true */
        $Olangda = false;	/* Language in output is Danish (dansk) if true */
        $Operiodc21 = false;	/* Historical period in output is contemporary (efterkrigstiden) if true */
        $Opresnml = false;	/* Assemblage in output is normal if true */
        $OfacetposUni = false;	/* Style of type of content PoS-tags (PoS-tags) in output is Universal Part-of-Speech Tagset if true */
        $OformatconllclU = false;	/* Style of format CoNLL in output is CoNLL-U (10 columns)CoNLL-U (10 kolonner) if true */

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
            $Iambiguna = existsArgumentWithValue("Iambig", "una");
            $echos = $echos . "Iambiguna=$Iambiguna ";
            }
        if( hasArgument("Iapp") )
            {
            $Iappnrm = existsArgumentWithValue("Iapp", "nrm");
            $echos = $echos . "Iappnrm=$Iappnrm ";
            }
        if( hasArgument("Ifacet") )
            {
            $Ifacetseg = existsArgumentWithValue("Ifacet", "seg");
            $Ifacettok = existsArgumentWithValue("Ifacet", "tok");
            $Ifacettxt = existsArgumentWithValue("Ifacet", "txt");
            $echos = $echos . "Ifacetseg=$Ifacetseg " . "Ifacettok=$Ifacettok " . "Ifacettxt=$Ifacettxt ";
            }
        if( hasArgument("Iformat") )
            {
            $Iformatflat = existsArgumentWithValue("Iformat", "flat");
            $Iformatteip5 = existsArgumentWithValue("Iformat", "teip5");
            $Iformattxtann = existsArgumentWithValue("Iformat", "txtann");
            $echos = $echos . "Iformatflat=$Iformatflat " . "Iformatteip5=$Iformatteip5 " . "Iformattxtann=$Iformattxtann ";
            }
        if( hasArgument("Ilang") )
            {
            $Ilangda = existsArgumentWithValue("Ilang", "da");
            $echos = $echos . "Ilangda=$Ilangda ";
            }
        if( hasArgument("Iperiod") )
            {
            $Iperiodc21 = existsArgumentWithValue("Iperiod", "c21");
            $echos = $echos . "Iperiodc21=$Iperiodc21 ";
            }
        if( hasArgument("Ipres") )
            {
            $Ipresnml = existsArgumentWithValue("Ipres", "nml");
            $echos = $echos . "Ipresnml=$Ipresnml ";
            }
        if( hasArgument("Oambig") )
            {
            $Oambiguna = existsArgumentWithValue("Oambig", "una");
            $echos = $echos . "Oambiguna=$Oambiguna ";
            }
        if( hasArgument("Oapp") )
            {
            $Oappnrm = existsArgumentWithValue("Oapp", "nrm");
            $echos = $echos . "Oappnrm=$Oappnrm ";
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacetlem = existsArgumentWithValue("Ofacet", "lem");
            $Ofacetmrf = existsArgumentWithValue("Ofacet", "mrf");
            $Ofacetpos = existsArgumentWithValue("Ofacet", "pos");
            $Ofacetseg = existsArgumentWithValue("Ofacet", "seg");
            $Ofacetstx = existsArgumentWithValue("Ofacet", "stx");
            $Ofacettok = existsArgumentWithValue("Ofacet", "tok");
            $echos = $echos . "Ofacetlem=$Ofacetlem " . "Ofacetmrf=$Ofacetmrf " . "Ofacetpos=$Ofacetpos " . "Ofacetseg=$Ofacetseg " . "Ofacetstx=$Ofacetstx " . "Ofacettok=$Ofacettok ";
            }
        if( hasArgument("Oformat") )
            {
            $Oformatconll = existsArgumentWithValue("Oformat", "conll");
            $Oformatteip5 = existsArgumentWithValue("Oformat", "teip5");
            $Oformattxtann = existsArgumentWithValue("Oformat", "txtann");
            $echos = $echos . "Oformatconll=$Oformatconll " . "Oformatteip5=$Oformatteip5 " . "Oformattxtann=$Oformattxtann ";
            }
        if( hasArgument("Olang") )
            {
            $Olangda = existsArgumentWithValue("Olang", "da");
            $echos = $echos . "Olangda=$Olangda ";
            }
        if( hasArgument("Operiod") )
            {
            $Operiodc21 = existsArgumentWithValue("Operiod", "c21");
            $echos = $echos . "Operiodc21=$Operiodc21 ";
            }
        if( hasArgument("Opres") )
            {
            $Opresnml = existsArgumentWithValue("Opres", "nml");
            $echos = $echos . "Opresnml=$Opresnml ";
            }

/*******************************
* input/output features styles *
*******************************/
        if( hasArgument("Ofacetpos") )
            {
            $OfacetposUni = existsArgumentWithValue("Ofacetpos", "Uni");
            $echos = $echos . "OfacetposUni=$OfacetposUni ";
            }
        if( hasArgument("Oformatconll") )
            {
            $OformatconllclU = existsArgumentWithValue("Oformatconll", "clU");
            $echos = $echos . "OformatconllclU=$OformatconllclU ";
            }

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $dapipefile = tempFileName("dapipe-results");
        $command = "echo $echos >> $dapipefile";
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
        logit("F:" . $F);
        if($Iformatflat)
            {
            logit("Flat");
            $dapipefile = dapipe($F);
            $tmpf = fopen($dapipefile,'r');

            if($tmpf)
                {
                //logit('output from dapipe:');
                while($line = fgets($tmpf))
                    {
                    //logit($line);
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
        else 
            {
            if($Ifacettok) // and also $Ifacetseg!
                {
                logit("segments and tokens input, PoS,morphology,Lemmas,syntax output");
                $dapipefile = tempFileName("dapipe-results");
                logit("dapipefile $dapipefile");
                $tmp1 = tempFileName("dapipe-tmp1");
                $tmp2 = tempFileName("dapipe-tmp2");
                $command = "../bin/bracmat \"get'\\\"dapipex.bra\\\"\" $IfacettokF $IfacetsegF $dapipefile $tmp1 $tmp2";
                $rms = "&& rm $IfacettokF && rm $IfacetsegF ";
                }
            else
                {
                logit("TEI P5 input");
                $dapipefile = tempFileName("dapipe-results");
                logit("dapipefile $dapipefile");
                $tmp1 = tempFileName("dapipe-tmp1");
                $tmp2 = tempFileName("dapipe-tmp2");
                $command = "../bin/bracmat \"get'\\\"dapipe.bra\\\"\" $F $dapipefile $tmp1 $tmp2";
                $rms = " && rm $F";
                }
            $command .= " && curl -v -F job=$job -F name=$dapipefile -F data=@$dapipefile $post2 && rm $tmp1 && rm $tmp2 " . $rms  . " && rm $dapipefile && rm $F > ../log/dapipe.log 2>&1 &";
            logit($command);
            exec($command);

            logit('RETURN 202');
            header("HTTP/1.0 202 Accepted");
            }
//*/
        }
    loginit();
    do_dapipe();
    }
catch (SystemExit $e) 
    { 
    header('HTTP/1.0 404 An error occurred: ' . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }
?>

