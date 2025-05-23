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
ToolID         : CQP
PassWord       :
Version        : 1
Title          : CQP formatter
Path in URL    : cqpConverter	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : CST
ContentProvider: CST
Creator        : Bart Jongejan
InfoAbout      : --
Description    : Takes input comntaining words, tags and lemmas and creates output that can be read by the CQP software.
ExternalURI    :
XMLparms       :
PostData       :
Inactive       :
 */

/*******************
 * helper functions *
 *******************/
$toollog = '../log/CQP.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */

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

function scripinit($inputF,$input,$output)  /* Initialises outputfile. */
{
    global $fscrip, $CQPfile;
    $fscrip = fopen($CQPfile,'w');
    if($fscrip)
    {
        fwrite($fscrip,"/*\n");
        fwrite($fscrip," * ToolID           : CQP\n");
        fwrite($fscrip," * Version          : 1\n");
        fwrite($fscrip," * Title            : CQP formatter\n");
        fwrite($fscrip," * ServiceURL       : http://localhost/cqpConverter\n");
        fwrite($fscrip," * Publisher        : CST\n");
        fwrite($fscrip," * ContentProvider  : CST\n");
        fwrite($fscrip," * Creator          : Bart Jongejan\n");
        fwrite($fscrip," * InfoAbout        : --\n");
        fwrite($fscrip," * Description      : Takes input comntaining words, tags and lemmas and creates output that can be read by the CQP software.\n");
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
    global $fscrip, $CQPfile;
    $fscrip = fopen($CQPfile,'a');
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
                $tempfilename = tempFileName("CQP_{$requestParm}_");
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

    function CQP4($IfacettokF,$IfacetsegF,$IfacetposF,$IfacetlemF,$Ifacet_seg_tokF,$today)
    {
        global $mode;
        logit("cqp4($IfacettokF,$IfacetsegF,$IfacetposF,$IfacetlemF,$Ifacet_seg_tokF)");

        $cqp4file = tempFileName("cqp4-results");

        logit("cqp4file $cqp4file");

        if($mode == 'dry')
        {
            scrip("../bin/bracmat 'get\$\"cqp.bra\"' \$CQPfile \$IfacettokF \$IfacetsegF \$IfacetposF \$IfacetlemF \$Ifacet_seg_tokF $today");
        }
        else
        {
            /*copy($IfacettokF,"IfacettokF");
            copy($IfacetsegF,"IfacetsegF");
            copy($IfacetposF,"IfacetposF");
            copy($IfacetlemF,"IfacetlemF");
            copy($Ifacet_seg_tokF,"Ifacet_seg_tokF");
            */
            $command = "../bin/bracmat 'get\$\"cqp.bra\"' $cqp4file $IfacettokF $IfacetsegF $IfacetposF $IfacetlemF $Ifacet_seg_tokF $today";

            logit($command);

            if(($cmd = popen($command, "r")) == NULL)
            {
                throw new SystemExit(); // instead of exit()
            }

            while($read = fgets($cmd))
            {
            }

            pclose($cmd);
            //copy($cqp4file,"cqp4file");
        }
        return $cqp4file;
    }

    function CQP($filename)
    {
        global $mode;
        $outp = tempFileName("CQP");
        if($mode == 'dry')
            scrip("python3 pyvrt.py \$F \$CQPfile");
        else
        {
            $command = 'python3 pyvrt.py ' . $filename . ' ' . $outp;
            $command = trim($command);

            logit("$command");
            if(($cmd = popen($command, "r")) == NULL)
            {
                throw new SystemExit(); // instead of exit()
            }

            while($read = fgets($cmd))
            {
            }
            pclose($cmd);
        }
        return $outp;
    }

    function do_CQP()
    {
        global $CQPfile;
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
        $F = "";	/* Input (ONLY used if there is exactly ONE input to this workflow step) */
        $Ifacet_seg_tokF = "";	/* Input with type of content segments (sætningssegmenter) and tokens (tokens) */
        $IfacetlemF = "";	/* Input with type of content lemmas (lemmaer) */
        $IfacetposF = "";	/* Input with type of content PoS-tags (PoS-tags) */
        $IfacetsegF = "";	/* Input with type of content segments (sætningssegmenter) */
        $IfacettokF = "";	/* Input with type of content tokens (tokens) */
        $Iambiguna = false;	/* Ambiguity in input is unambiguous (utvetydig) if true */
        $Ifacet_lem_pos_seg_tok = false;	/* Type of content in input is lemmas (lemmaer) and PoS-tags (PoS-tags) and segments (sætningssegmenter) and tokens (tokens) if true */
        $Ifacet_seg_tok = false;	/* Type of content in input is segments (sætningssegmenter) and tokens (tokens) if true */
        $Ifacetlem = false;	/* Type of content in input is lemmas (lemmaer) if true */
        $Ifacetpos = false;	/* Type of content in input is PoS-tags (PoS-tags) if true */
        $Ifacetseg = false;	/* Type of content in input is segments (sætningssegmenter) if true */
        $Ifacettok = false;	/* Type of content in input is tokens (tokens) if true */
        $Iformatcols = false;	/* Format in input is columns, tab separated fields (kolonner, tab separeret) if true */
        $Iformatteip5 = false;	/* Format in input is TEIP5DKCLARIN_ANNOTATION if true */
        $Ipresnml = false;	/* Assemblage in input is normal if true */
        $Oambiguna = false;	/* Ambiguity in output is unambiguous (utvetydig) if true */
        $Ofacetcls = false;	/* Type of content in output is word class (ordklasse) if true */
        $Ofacetlem = false;	/* Type of content in output is lemmas (lemmaer) if true */
        $Ofacetpos = false;	/* Type of content in output is PoS-tags (PoS-tags) if true */
        $Ofacetseg = false;	/* Type of content in output is segments (sætningssegmenter) if true */
        $Ofacettok = false;	/* Type of content in output is tokens (tokens) if true */
        $Oformatvrt = false;	/* Format in output is Corpus Workbench (for CQP queries) if true */
        $Opresnml = false;	/* Assemblage in output is normal if true */

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
            if($F == '')
            {
                header("HTTP/1.0 404 Input not found (F parameter). ");
                return;
            }
            $echos = $echos . "F=$F ";
            $inputF = $inputF . " \$F ";
        }
        if( hasArgument("Ifacet_seg_tokF") )
        {
            $Ifacet_seg_tokF = requestFile("Ifacet_seg_tokF");
            if($Ifacet_seg_tokF == '')
            {
                header("HTTP/1.0 404 Input with type of content 'segments (sætningssegmenter) and tokens (tokens)' not found (Ifacet_seg_tokF parameter). ");
                return;
            }
            $echos = $echos . "Ifacet_seg_tokF=$Ifacet_seg_tokF ";
            $inputF = $inputF . " \$Ifacet_seg_tokF ";
        }
        if( hasArgument("IfacetlemF") )
        {
            $IfacetlemF = requestFile("IfacetlemF");
            if($IfacetlemF == '')
            {
                header("HTTP/1.0 404 Input with type of content 'lemmas (lemmaer)' not found (IfacetlemF parameter). ");
                return;
            }
            $echos = $echos . "IfacetlemF=$IfacetlemF ";
            $inputF = $inputF . " \$IfacetlemF ";
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
            $inputF = $inputF . " \$IfacetposF ";
        }
        if( hasArgument("IfacetsegF") )
        {
            $IfacetsegF = requestFile("IfacetsegF");
            if($IfacetsegF == '')
            {
                header("HTTP/1.0 404 Input with type of content 'segments (sætningssegmenter)' not found (IfacetsegF parameter). ");
                return;
            }
            $echos = $echos . "IfacetsegF=$IfacetsegF ";
            $inputF = $inputF . " \$IfacetsegF ";
        }
        if( hasArgument("IfacettokF") )
        {
            $IfacettokF = requestFile("IfacettokF");
            if($IfacettokF == '')
            {
                header("HTTP/1.0 404 Input with type of content 'tokens (tokens)' not found (IfacettokF parameter). ");
                return;
            }
            $echos = $echos . "IfacettokF=$IfacettokF ";
            $inputF = $inputF . " \$IfacettokF ";
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
            $Ifacet_lem_pos_seg_tok = existsArgumentWithValue("Ifacet", "_lem_pos_seg_tok");
            $Ifacet_seg_tok = existsArgumentWithValue("Ifacet", "_seg_tok");
            $Ifacetlem = existsArgumentWithValue("Ifacet", "lem");
            $Ifacetpos = existsArgumentWithValue("Ifacet", "pos");
            $Ifacetseg = existsArgumentWithValue("Ifacet", "seg");
            $Ifacettok = existsArgumentWithValue("Ifacet", "tok");
            $echos = $echos . "Ifacet_lem_pos_seg_tok=$Ifacet_lem_pos_seg_tok " . "Ifacet_seg_tok=$Ifacet_seg_tok " . "Ifacetlem=$Ifacetlem " . "Ifacetpos=$Ifacetpos " . "Ifacetseg=$Ifacetseg " . "Ifacettok=$Ifacettok ";
            $input = $input . ($Ifacet_lem_pos_seg_tok ? " \$Ifacet_lem_pos_seg_tok" : "")  . ($Ifacet_seg_tok ? " \$Ifacet_seg_tok" : "")  . ($Ifacetlem ? " \$Ifacetlem" : "")  . ($Ifacetpos ? " \$Ifacetpos" : "")  . ($Ifacetseg ? " \$Ifacetseg" : "")  . ($Ifacettok ? " \$Ifacettok" : "") ;
        }
        if( hasArgument("Iformat") )
        {
            $Iformatcols = existsArgumentWithValue("Iformat", "cols");
            $Iformatteip5 = existsArgumentWithValue("Iformat", "teip5");
            $echos = $echos . "Iformatcols=$Iformatcols " . "Iformatteip5=$Iformatteip5 ";
            $input = $input . ($Iformatcols ? " \$Iformatcols" : "")  . ($Iformatteip5 ? " \$Iformatteip5" : "") ;
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
            $Ofacetcls = existsArgumentWithValue("Ofacet", "cls");
            $Ofacetlem = existsArgumentWithValue("Ofacet", "lem");
            $Ofacetpos = existsArgumentWithValue("Ofacet", "pos");
            $Ofacetseg = existsArgumentWithValue("Ofacet", "seg");
            $Ofacettok = existsArgumentWithValue("Ofacet", "tok");
            $echos = $echos . "Ofacetcls=$Ofacetcls " . "Ofacetlem=$Ofacetlem " . "Ofacetpos=$Ofacetpos " . "Ofacetseg=$Ofacetseg " . "Ofacettok=$Ofacettok ";
            $output = $output . ($Ofacetcls ? " \$Ofacetcls" : "")  . ($Ofacetlem ? " \$Ofacetlem" : "")  . ($Ofacetpos ? " \$Ofacetpos" : "")  . ($Ofacetseg ? " \$Ofacetseg" : "")  . ($Ofacettok ? " \$Ofacettok" : "") ;
        }
        if( hasArgument("Oformat") )
        {
            $Oformatvrt = existsArgumentWithValue("Oformat", "vrt");
            $echos = $echos . "Oformatvrt=$Oformatvrt ";
            $output = $output . ($Oformatvrt ? " \$Oformatvrt" : "") ;
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

        /* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $CQPfile = tempFileName("CQP-results");
        $command = "echo $echos >> $CQPfile";
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
        $today = date("Ymd");
        if($mode == 'dry')
        {
            $CQPfile = tempFileName("CQP-results");
            scripinit($inputF,$input,$output);
            if($Iformatteip5)
                CQP4($IfacettokF,$IfacetsegF,$IfacetposF,$IfacetlemF,$Ifacet_seg_tokF,$today);
            else
                CQP($F);
        }
        else
        {
            if($Iformatteip5)
                $CQPfile = CQP4($IfacettokF,$IfacetsegF,$IfacetposF,$IfacetlemF,$Ifacet_seg_tokF,$today);
            else
                $CQPfile = CQP($F);

            logit("CQPfile $CQPfile");
        }
        // YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $CQPfile
        //*/
        $tmpf = fopen($CQPfile,'r');

        if($tmpf)
        {
            //logit('output from CQP:');
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
    do_CQP();
}
catch (SystemExit $e)
{
    header('HTTP/1.0 404 An error occurred: ' . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
}
?>

