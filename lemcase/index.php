<?php
header('Content-type:text/plain; charset=UTF-8');
/*
 * This PHP script is generated by CLARIN-DK's tool registration form
 * (http://localhost/texton/register). It should, with no or few adaptations
 * work out of the box as a dummy for your web service. The output returned
 * to the Text Tonsorium (CLARIN-DK's workflow manager) is just a listing of
 * the HTTP parameters received by this web service from the Text Tonsorium,
 * and not the output proper. For that you have to add your code to this script
 * and deactivate the dummy functionality. (The comments near the end of this
 * script explain how that is done.)
 *
 * Places in this script that require your attention are marked 'TODO'.
 */
/*
ToolID         : lemcase
PassWord       : 
Version        : 1.0
Title          : Lemma Case Rectifier
Path in URL    : lemcase	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : CST
ContentProvider: CST
Creator        : Bart Jongejan
InfoAbout      : -
Description    : Take lemmas and PoS tags and, using PoS info, decide how the casing of the lemma should be and correct, if necessary.
ExternalURI    : 
RestAPIkey         : 
RestAPIpassword    : 
MultiInp       : 
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/lemcase.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */

/*  TODO Set $dodelete to false if temporary files in /tmp should not be deleted before returning. */
$dodelete = true;
$tobedeleted = array();

function loginit()  /* Wipes the contents of the log file! TODO Change this behaviour if needed. */
    {
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
    global $toollog,$ftemp;
    $ftemp = fopen($toollog,'a');
    if($ftemp)
        {
        fwrite($ftemp,$str . "\n");
        fclose($ftemp);
        }
    }

function scripinit($inputF,$input,$output)  /* Initialises outputfile. */
    {
    global $fscrip, $lemcasefile;
    $fscrip = fopen($lemcasefile,'w');
    if($fscrip)
        {
        fwrite($fscrip,"/*\n");
        fwrite($fscrip," * ToolID           : lemcase\n");
        fwrite($fscrip," * Version          : 1.0\n");
        fwrite($fscrip," * Title            : Lemma Case Rectifier\n");
        fwrite($fscrip," * ServiceURL       : http://localhost/lemcase\n");
        fwrite($fscrip," * Publisher        : CST\n");
        fwrite($fscrip," * ContentProvider  : CST\n");
        fwrite($fscrip," * Creator          : Bart Jongejan\n");
        fwrite($fscrip," * InfoAbout        : -\n");
        fwrite($fscrip," * Description      : Take lemmas and PoS tags and, using PoS info, decide how the casing of the lemma should be and correct, if necessary.\n");
        fwrite($fscrip," * ExternalURI      : \n");
        fwrite($fscrip," * inputF " . $inputF . "\n");
        fwrite($fscrip," * input  " . $input  . "\n");
        fwrite($fscrip," * output " . $output . "\n");
        fwrite($fscrip," */\n");
        fwrite($fscrip,"\ncd " . getcwd() . "\n");
        fclose($fscrip);
        }
    }

function scrip($str) /* TODO send comments and command line instructions. Don't forget to terminate string with new line character, if needed.*/
    {
    global $fscrip, $lemcasefile;
    $fscrip = fopen($lemcasefile,'a');
    if($fscrip)
        {
        fwrite($fscrip,$str . "\n");
        fclose($fscrip);
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
            if($parameterName === urldecode($name) && $parameterValue === urldecode($value))
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
                $tempfilename = tempFileName("lemcase_{$requestParm}_");
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

    function do_lemcase()
        {
        global $lemcasefile;
        global $dodelete;
        global $tobedeleted;
        global $mode;
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
        $mode = "";	/* If the value is 'dry', the wrapper is expected to return a script of what will be done if the value is not 'dry', but 'run'. */
        $inputF = "";	/* List of all input files. */
        $input = "";	/* List of all input features. */
        $output = "";	/* List of all output features. */
        $echos = "";	/* List arguments and their actual values. For sanity check of this generated script. All references to this variable can be removed once your web service is working as intended. */
        $IfacetlemF = "";	/* Input with annotationstype lemmas (lemmaer) */
        $IfacetposF = "";	/* Input with annotationstype PoS-tags (PoS-tags) */
        $Iambiguna = false;	/* Flertydighed in input is unambiguous (utvetydig) if true */
        $Ifacetlem = false;	/* Annotationstype in input is lemmas (lemmaer) if true */
        $Ifacetpos = false;	/* Annotationstype in input is PoS-tags (PoS-tags) if true */
        $Iformatteip5 = false;	/* Format in input is TEIP5 if true */
        $Ilanggml = false;	/* Sprog in input is Middle Low German (middelnedertysk) if true */
        $Iperiodc13 = false;	/* Historisk periode in input is medieval (middelalderen) if true */
        $Ipresnml = false;	/* Sammensætning in input is normal if true */
        $Oambiguna = false;	/* Flertydighed in output is unambiguous (utvetydig) if true */
        $Ofacetlem = false;	/* Annotationstype in output is lemmas (lemmaer) if true */
        $Oformatteip5 = false;	/* Format in output is TEIP5 if true */
        $Olanggml = false;	/* Sprog in output is Middle Low German (middelnedertysk) if true */
        $Operiodc13 = false;	/* Historisk periode in output is medieval (middelalderen) if true */
        $Opresnml = false;	/* Sammensætning in output is normal if true */
        $Ifacetlemasi = false;	/* Style of annotationstype lemmas (lemmaer) in input is as-issom ordet if true */
        $IfacetposHiNTS = false;	/* Style of annotationstype PoS-tags (PoS-tags) in input is HiNTS (Historisches-Niederdeutsch-Tagset) if true */
        $Ofacetlemnam = false;	/* Style of annotationstype lemmas (lemmaer) in output is names capitalisednavne med stort if true */

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
        if( hasArgument("mode") )
            {
            $mode = getArgument("mode");
            }
        $echos = "base=$base job=$job post2=$post2 mode=$mode ";

/*********
* input  *
*********/
        if( hasArgument("IfacetlemF") )
            {
            $IfacetlemF = requestFile("IfacetlemF");
            if($IfacetlemF === '')
                {
                header("HTTP/1.0 404 Input with annotationstype 'lemmas (lemmaer)' not found (IfacetlemF parameter). ");
                return;
                }
            $echos = $echos . "IfacetlemF=$IfacetlemF ";
            $inputF = $inputF . " \$IfacetlemF ";
            }
        if( hasArgument("IfacetposF") )
            {
            $IfacetposF = requestFile("IfacetposF");
            if($IfacetposF === '')
                {
                header("HTTP/1.0 404 Input with annotationstype 'PoS-tags (PoS-tags)' not found (IfacetposF parameter). ");
                return;
                }
            $echos = $echos . "IfacetposF=$IfacetposF ";
            $inputF = $inputF . " \$IfacetposF ";
            }

/************************
* input/output features *
************************/
        if( hasArgument("Iambig") )
            {
            $Iambiguna = existsArgumentWithValue("Iambig", "una");
            $echos = $echos . "Iambiguna=$Iambiguna ";
            $input = $input . ($Iambiguna ? " \$Iambiguna" : "") ;
            }
        if( hasArgument("Ifacet") )
            {
            $Ifacetlem = existsArgumentWithValue("Ifacet", "lem");
            $Ifacetpos = existsArgumentWithValue("Ifacet", "pos");
            $echos = $echos . "Ifacetlem=$Ifacetlem " . "Ifacetpos=$Ifacetpos ";
            $input = $input . ($Ifacetlem ? " \$Ifacetlem" : "")  . ($Ifacetpos ? " \$Ifacetpos" : "") ;
            }
        if( hasArgument("Iformat") )
            {
            $Iformatteip5 = existsArgumentWithValue("Iformat", "teip5");
            $echos = $echos . "Iformatteip5=$Iformatteip5 ";
            $input = $input . ($Iformatteip5 ? " \$Iformatteip5" : "") ;
            }
        if( hasArgument("Ilang") )
            {
            $Ilanggml = existsArgumentWithValue("Ilang", "gml");
            $echos = $echos . "Ilanggml=$Ilanggml ";
            $input = $input . ($Ilanggml ? " \$Ilanggml" : "") ;
            }
        if( hasArgument("Iperiod") )
            {
            $Iperiodc13 = existsArgumentWithValue("Iperiod", "c13");
            $echos = $echos . "Iperiodc13=$Iperiodc13 ";
            $input = $input . ($Iperiodc13 ? " \$Iperiodc13" : "") ;
            }
        if( hasArgument("Ipres") )
            {
            $Ipresnml = existsArgumentWithValue("Ipres", "nml");
            $echos = $echos . "Ipresnml=$Ipresnml ";
            $input = $input . ($Ipresnml ? " \$Ipresnml" : "") ;
            }
        if( hasArgument("Oambig") )
            {
            $Oambiguna = existsArgumentWithValue("Oambig", "una");
            $echos = $echos . "Oambiguna=$Oambiguna ";
            $output = $output . ($Oambiguna ? " \$Oambiguna" : "") ;
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacetlem = existsArgumentWithValue("Ofacet", "lem");
            $echos = $echos . "Ofacetlem=$Ofacetlem ";
            $output = $output . ($Ofacetlem ? " \$Ofacetlem" : "") ;
            }
        if( hasArgument("Oformat") )
            {
            $Oformatteip5 = existsArgumentWithValue("Oformat", "teip5");
            $echos = $echos . "Oformatteip5=$Oformatteip5 ";
            $output = $output . ($Oformatteip5 ? " \$Oformatteip5" : "") ;
            }
        if( hasArgument("Olang") )
            {
            $Olanggml = existsArgumentWithValue("Olang", "gml");
            $echos = $echos . "Olanggml=$Olanggml ";
            $output = $output . ($Olanggml ? " \$Olanggml" : "") ;
            }
        if( hasArgument("Operiod") )
            {
            $Operiodc13 = existsArgumentWithValue("Operiod", "c13");
            $echos = $echos . "Operiodc13=$Operiodc13 ";
            $output = $output . ($Operiodc13 ? " \$Operiodc13" : "") ;
            }
        if( hasArgument("Opres") )
            {
            $Opresnml = existsArgumentWithValue("Opres", "nml");
            $echos = $echos . "Opresnml=$Opresnml ";
            $output = $output . ($Opresnml ? " \$Opresnml" : "") ;
            }

/*******************************
* input/output features styles *
*******************************/
        if( hasArgument("Ifacetlem") )
            {
            $Ifacetlemasi = existsArgumentWithValue("Ifacetlem", "asi");
            $echos = $echos . "Ifacetlemasi=$Ifacetlemasi ";
            $input = $input . ($Ifacetlemasi ? " \$Ifacetlemasi" : "") ;
            }
        if( hasArgument("Ifacetpos") )
            {
            $IfacetposHiNTS = existsArgumentWithValue("Ifacetpos", "HiNTS");
            $echos = $echos . "IfacetposHiNTS=$IfacetposHiNTS ";
            $input = $input . ($IfacetposHiNTS ? " \$IfacetposHiNTS" : "") ;
            }
        if( hasArgument("Ofacetlem") )
            {
            $Ofacetlemnam = existsArgumentWithValue("Ofacetlem", "nam");
            $echos = $echos . "Ofacetlemnam=$Ofacetlemnam ";
            $output = $output . ($Ofacetlemnam ? " \$Ofacetlemnam" : "") ;
            }

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $lemcasefile = tempFileName("lemcase-results");
        $command = "echo $echos >> $lemcasefile";
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
        $lemcasefile = tempFileName("lemcase-results");
        if($mode === 'dry')
            scripinit($inputF,$input,$output);
        else
            {
//            copy($IfacetposF,"IfacetposF");
//            copy($IfacetlemF,"IfacetlemF");
            $command = "../bin/bracmat 'get\$\"lemcase.bra\"' '$IfacetlemF' '$IfacetposF' '$lemcasefile'";
            logit($command);
            if(($cmd = popen($command, "r")) == NULL)
                exit(1);

            while($read = fgets($cmd))
                {
                }
            }
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $lemcasefile
//*/
        $tmpf = fopen($lemcasefile,'r');

        if($tmpf)
            {
            //logit('output from lemcase:');
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
    loginit();
    do_lemcase();
    }
catch (SystemExit $e)
    {
    header('HTTP/1.0 404 An error occurred: ' . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }
?>

