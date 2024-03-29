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
ToolID         : CST-Ver
PassWord       : 
Version        : 0
Title          : Document similarity checker
Path in URL    : rep-check-multi/	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : CST
ContentProvider: cst.ku.dk
Creator        : Bart Jongejan
InfoAbout      : https://nlpweb01.nors.ku.dk/online/rep_check/
Description    : Uses a statistical method to find phrases that are found in each of the input documents.
ExternalURI    : 
MultiInp       : y
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/CSTVer.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */

/*  TODO Set $dodelete to false if temporary files in /tmp should not be deleted before returning. */
$dodelete = true;
$tobedeleted = array();
$params = array();

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
    global $fscrip, $CSTVerfile;
    $fscrip = fopen($CSTVerfile,'w');
    if($fscrip)
        {
        fwrite($fscrip,"/*\n");
        fwrite($fscrip," * ToolID           : CST-Ver\n");
        fwrite($fscrip," * Version          : 0\n");
        fwrite($fscrip," * Title            : Document similarity checker\n");
        fwrite($fscrip," * ServiceURL       : http://localhost/rep-check-multi/\n");
        fwrite($fscrip," * Publisher        : CST\n");
        fwrite($fscrip," * ContentProvider  : cst.ku.dk\n");
        fwrite($fscrip," * Creator          : Bart Jongejan\n");
        fwrite($fscrip," * InfoAbout        : https://nlpweb01.nors.ku.dk/online/rep_check/\n");
        fwrite($fscrip," * Description      : Uses a statistical method to find phrases that are found in each of the input documents.\n");
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
    global $fscrip, $CSTVerfile;
    $fscrip = fopen($CSTVerfile,'a');
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
        global $params; // $params can contain multiple parameters with the same name. $_REQUEST and $_GET remove duplicates.
        $inputfiles = array();
        
        if(isset($params[$requestParm]))
            {
            $urlbase = isset($params["base"]) ? $params["base"][0] : "http://localhost/toolsdata/";
            $items = $params[$requestParm];
            foreach($items as $item)
                {
                $url = $urlbase . urlencode($item);

                $handle = fopen($url, "r");
                if($handle == false)
                    {
                    logit("Cannot open url[$url]");
                    return "";
                    }
                else
                    {
                    $tempfilename = tempFileName("CSTVer_{$requestParm}_");
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
                        $inputfiles[$tempfilename] = $item;
                        //return $tempfilename;
                        }
                    }
                }
            }
        return $inputfiles;
        }

    function gentagelseschecker($filename,$job)
        {
        global $dodelete;
        global $tobedeleted;
        global $mode;
        if($dodelete)
            foreach($filename as $name => $value)
                {
                $tobedeleted["$name.html"] = true;
                }

        $arr = array();
        foreach($filename as $name => $value)
            {
            $arr[] = $name;
            }
        $filenames = implode(' ',$arr);

        if($mode == 'dry')
            {
            scrip("../bin/repver -w4 <filename> <filename> ...");
            }
        else
            {
            $command = '../bin/repver -w4 ' . $filenames;
            $command = trim($command);

            logit("$command");

            $tmpo = tempFileName("REP");

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
            $job = strstr($job, "tep", true);
            foreach($filename as $name => $value)
                {
                $name  = str_replace("/","\/", $name );
                $value = str_replace("/","\/", $value);
                $value = strstr($value, "-".$job, true);
                system("sed -i 's/{$name}/{$value}/' $tmpo");
                }

            pclose($cmd);
            }
        return $tmpo;
        }

    function do_CSTVer()
        {
        global $CSTVerfile;
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
        $F = "";	/* Input (ONLY used if there is exactly ONE kind of input to this workflow step) */
        $Iambiguna = false;	/* Ambiguity in input is unambiguous (utvetydig) if true */
        $Ifacet_lem_seg = false;	/* Type of content in input is lemmas (lemmaer) and segments (sætningssegmenter) if true */
        $Ifacet_pos_seg = false;	/* Type of content in input is PoS-tags (PoS-tags) and segments (sætningssegmenter) if true */
        $Ifacet_seg_tok = false;	/* Type of content in input is segments (sætningssegmenter) and tokens (tokens) if true */
        $Ifacettxt = false;	/* Type of content in input is text (ingen annotation) if true */
        $Iformatflat = false;	/* Format in input is plain (flad) if true */
        $Ipresnml = false;	/* Assemblage in input is normal if true */
        $Oambiguna = false;	/* Ambiguity in output is unambiguous (utvetydig) if true */
        $Ofacetrep = false;	/* Type of content in output is repeated phrases (gentagelser) if true */
        $Oformathtml = false;	/* Format in output is HTML if true */
        $Opresfrq = false;	/* Assemblage in output is frequency list (frekvensliste) if true */
        $OformathtmlROTM = false;	/* Style of format HTML in output is Traditional tags (h, p, etc.)Med traditionelle tags (h, p, etc.) if true */

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
        if( hasArgument("F") )
            {
            $F = requestFile("F");
            if($F === '')
                {
                header("HTTP/1.0 404 Input not found (F parameter). ");
                return;
                }
            $echos = $echos . "F=$F ";
            $inputF = $inputF . " \$F ";
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
            $Ifacet_lem_seg = existsArgumentWithValue("Ifacet", "_lem_seg");
            $Ifacet_pos_seg = existsArgumentWithValue("Ifacet", "_pos_seg");
            $Ifacet_seg_tok = existsArgumentWithValue("Ifacet", "_seg_tok");
            $Ifacettxt = existsArgumentWithValue("Ifacet", "txt");
            $echos = $echos . "Ifacet_lem_seg=$Ifacet_lem_seg " . "Ifacet_pos_seg=$Ifacet_pos_seg " . "Ifacet_seg_tok=$Ifacet_seg_tok " . "Ifacettxt=$Ifacettxt ";
            $input = $input . ($Ifacet_lem_seg ? " \$Ifacet_lem_seg" : "")  . ($Ifacet_pos_seg ? " \$Ifacet_pos_seg" : "")  . ($Ifacet_seg_tok ? " \$Ifacet_seg_tok" : "")  . ($Ifacettxt ? " \$Ifacettxt" : "") ;
            }
        if( hasArgument("Iformat") )
            {
            $Iformatflat = existsArgumentWithValue("Iformat", "flat");
            $echos = $echos . "Iformatflat=$Iformatflat ";
            $input = $input . ($Iformatflat ? " \$Iformatflat" : "") ;
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
            $Ofacetrep = existsArgumentWithValue("Ofacet", "rep");
            $echos = $echos . "Ofacetrep=$Ofacetrep ";
            $output = $output . ($Ofacetrep ? " \$Ofacetrep" : "") ;
            }
        if( hasArgument("Oformat") )
            {
            $Oformathtml = existsArgumentWithValue("Oformat", "html");
            $echos = $echos . "Oformathtml=$Oformathtml ";
            $output = $output . ($Oformathtml ? " \$Oformathtml" : "") ;
            }
        if( hasArgument("Opres") )
            {
            $Opresfrq = existsArgumentWithValue("Opres", "frq");
            $echos = $echos . "Opresfrq=$Opresfrq ";
            $output = $output . ($Opresfrq ? " \$Opresfrq" : "") ;
            }

/*******************************
* input/output features styles *
*******************************/
        if( hasArgument("Oformathtml") )
            {
            $OformathtmlROTM = existsArgumentWithValue("Oformathtml", "ROTM");
            $echos = $echos . "OformathtmlROTM=$OformathtmlROTM ";
            $output = $output . ($OformathtmlROTM ? " \$OformathtmlROTM" : "") ;
            }

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $CSTVerfile = tempFileName("CSTVer-results");
        $command = "echo $echos >> $CSTVerfile";
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
        $CSTVerfile = tempFileName("CSTVer-results");
        if($mode == 'dry')
            {
            scripinit($inputF,$input,$output);
            gentagelseschecker("\$F",$job);
            }
        else
            {
            $CSTVerfile = gentagelseschecker($F,$job);
            }
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $CSTVerfile
//*/
        $tmpf = fopen($CSTVerfile,'r');

        if($tmpf)
            {
            //logit('output from CSTVer:');
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
    $query  = explode('&', $_SERVER['QUERY_STRING']);
    foreach( $query as $param )
        {
        if(strpos($param, '=') === false)
            $param .= '=';

        list($name, $value) = explode('=', $param, 2);
        $params[urldecode($name)][] = urldecode($value);
        }
    do_CSTVer();
    }
catch (SystemExit $e)
    {
    header('HTTP/1.0 404 An error occurred: ' . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }
?>

