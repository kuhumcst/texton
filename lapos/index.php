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
ToolID         : Lapos
PassWord       : 
Version        : 0.1.2
Title          : Lapos
Path in URL    : lapos	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : GitHub
ContentProvider: Perseus
Creator        : Yoshimasa Tsuruoka, Yusuke Miyao, and Jun'ichi Kazama
InfoAbout      : https://github.com/cltk/lapos
Description    : Fork of the Lookahead Part-Of-Speech (Lapos) Tagger
ExternalURI    : 
MultiInp       : 
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/Lapos.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */

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
    global $fscrip, $Laposfile;
    $fscrip = fopen($Laposfile,'w');
    if($fscrip)
        {
        fwrite($fscrip,"/*\n");
        fwrite($fscrip," * ToolID           : Lapos\n");
        fwrite($fscrip," * Version          : 0.1.2\n");
        fwrite($fscrip," * Title            : Lapos\n");
        fwrite($fscrip," * ServiceURL       : http://localhost/lapos\n");
        fwrite($fscrip," * Publisher        : GitHub\n");
        fwrite($fscrip," * ContentProvider  : Perseus\n");
        fwrite($fscrip," * Creator          : Yoshimasa Tsuruoka, Yusuke Miyao, and Jun'ichi Kazama\n");
        fwrite($fscrip," * InfoAbout        : https://github.com/cltk/lapos\n");
        fwrite($fscrip," * Description      : Fork of the Lookahead Part-Of-Speech (Lapos) Tagger\n");
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
    global $fscrip, $Laposfile;
    $fscrip = fopen($Laposfile,'a');
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
                $tempfilename = tempFileName("Lapos_{$requestParm}_");
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

    function do_Lapos()
        {
        global $Laposfile;
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
        $IfacetsegF = "";	/* Input with type of content segments (sætningssegmenter) */
        $IfacettokF = "";	/* Input with type of content tokens (tokens) */
        $Iambiguna = false;	/* Ambiguity in input is unambiguous (utvetydig) if true */
        $Ifacet_seg_tok = false;	/* Type of content in input is segments (sætningssegmenter) and tokens (tokens) if true */
        $Ifacetseg = false;	/* Type of content in input is segments (sætningssegmenter) if true */
        $Ifacettok = false;	/* Type of content in input is tokens (tokens) if true */
        $Iformatflat = false;	/* Format in input is plain (flad) if true */
        $Iformatteip5 = false;	/* Format in input is TEIP5DKCLARIN_ANNOTATION if true */
        $Ilangda = false;	/* Language in input is Danish (dansk) if true */
        $Ilanggml = false;	/* Language in input is Middle Low German (middelnedertysk) if true */
        $Ilangla = false;	/* Language in input is Latin (latin) if true */
        $Iperiodc13 = false;	/* Historical period in input is medieval (middelalderen) if true */
        $Iperiodc20 = false;	/* Historical period in input is late modern (moderne tid) if true */
        $Iperiodc21 = false;	/* Historical period in input is contemporary (efterkrigstiden) if true */
        $Ipresnml = false;	/* Assemblage in input is normal if true */
        $Oambiguna = false;	/* Ambiguity in output is unambiguous (utvetydig) if true */
        $Ofacetpos = false;	/* Type of content in output is PoS-tags (PoS-tags) if true */
        $Ofacetseg = false;	/* Type of content in output is segments (sætningssegmenter) if true */
        $Ofacettok = false;	/* Type of content in output is tokens (tokens) if true */
        $Oformatflat = false;	/* Format in output is plain (flad) if true */
        $Oformatteip5 = false;	/* Format in output is TEIP5DKCLARIN_ANNOTATION if true */
        $Olangda = false;	/* Language in output is Danish (dansk) if true */
        $Olanggml = false;	/* Language in output is Middle Low German (middelnedertysk) if true */
        $Olangla = false;	/* Language in output is Latin (latin) if true */
        $Operiodc13 = false;	/* Historical period in output is medieval (middelalderen) if true */
        $Operiodc20 = false;	/* Historical period in output is late modern (moderne tid) if true */
        $Operiodc21 = false;	/* Historical period in output is contemporary (efterkrigstiden) if true */
        $Opresnml = false;	/* Assemblage in output is normal if true */
        $OfacetposDSL = false;	/* Style of type of content PoS-tags (PoS-tags) in output is DSL-tagset if true */
        $OfacetposHiNTS = false;	/* Style of type of content PoS-tags (PoS-tags) in output is HiNTS (Historisches-Niederdeutsch-Tagset) if true */
        $OfacetposUni = false;	/* Style of type of content PoS-tags (PoS-tags) in output is Universal Part-of-Speech Tagset if true */

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
        if( hasArgument("IfacetsegF") )
            {
            $IfacetsegF = requestFile("IfacetsegF");
            if($IfacetsegF === '')
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
            if($IfacettokF === '')
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
            $Ifacet_seg_tok = existsArgumentWithValue("Ifacet", "_seg_tok");
            $Ifacetseg = existsArgumentWithValue("Ifacet", "seg");
            $Ifacettok = existsArgumentWithValue("Ifacet", "tok");
            $echos = $echos . "Ifacet_seg_tok=$Ifacet_seg_tok " . "Ifacetseg=$Ifacetseg " . "Ifacettok=$Ifacettok ";
            $input = $input . ($Ifacet_seg_tok ? " \$Ifacet_seg_tok" : "")  . ($Ifacetseg ? " \$Ifacetseg" : "")  . ($Ifacettok ? " \$Ifacettok" : "") ;
            }
        if( hasArgument("Iformat") )
            {
            $Iformatflat = existsArgumentWithValue("Iformat", "flat");
            $Iformatteip5 = existsArgumentWithValue("Iformat", "teip5");
            $echos = $echos . "Iformatflat=$Iformatflat " . "Iformatteip5=$Iformatteip5 ";
            $input = $input . ($Iformatflat ? " \$Iformatflat" : "")  . ($Iformatteip5 ? " \$Iformatteip5" : "") ;
            }
        if( hasArgument("Ilang") )
            {
            $Ilangda = existsArgumentWithValue("Ilang", "da");
            $Ilanggml = existsArgumentWithValue("Ilang", "gml");
            $Ilangla = existsArgumentWithValue("Ilang", "la");
            $echos = $echos . "Ilangda=$Ilangda " . "Ilanggml=$Ilanggml " . "Ilangla=$Ilangla ";
            $input = $input . ($Ilangda ? " \$Ilangda" : "")  . ($Ilanggml ? " \$Ilanggml" : "")  . ($Ilangla ? " \$Ilangla" : "") ;
            }
        if( hasArgument("Iperiod") )
            {
            $Iperiodc13 = existsArgumentWithValue("Iperiod", "c13");
            $Iperiodc20 = existsArgumentWithValue("Iperiod", "c20");
            $Iperiodc21 = existsArgumentWithValue("Iperiod", "c21");
            $echos = $echos . "Iperiodc13=$Iperiodc13 " . "Iperiodc20=$Iperiodc20 " . "Iperiodc21=$Iperiodc21 ";
            $input = $input . ($Iperiodc13 ? " \$Iperiodc13" : "")  . ($Iperiodc20 ? " \$Iperiodc20" : "")  . ($Iperiodc21 ? " \$Iperiodc21" : "") ;
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
            $Ofacetpos = existsArgumentWithValue("Ofacet", "pos");
            $Ofacetseg = existsArgumentWithValue("Ofacet", "seg");
            $Ofacettok = existsArgumentWithValue("Ofacet", "tok");
            $echos = $echos . "Ofacetpos=$Ofacetpos " . "Ofacetseg=$Ofacetseg " . "Ofacettok=$Ofacettok ";
            $output = $output . ($Ofacetpos ? " \$Ofacetpos" : "")  . ($Ofacetseg ? " \$Ofacetseg" : "")  . ($Ofacettok ? " \$Ofacettok" : "") ;
            }
        if( hasArgument("Oformat") )
            {
            $Oformatflat = existsArgumentWithValue("Oformat", "flat");
            $Oformatteip5 = existsArgumentWithValue("Oformat", "teip5");
            $echos = $echos . "Oformatflat=$Oformatflat " . "Oformatteip5=$Oformatteip5 ";
            $output = $output . ($Oformatflat ? " \$Oformatflat" : "")  . ($Oformatteip5 ? " \$Oformatteip5" : "") ;
            }
        if( hasArgument("Olang") )
            {
            $Olangda = existsArgumentWithValue("Olang", "da");
            $Olanggml = existsArgumentWithValue("Olang", "gml");
            $Olangla = existsArgumentWithValue("Olang", "la");
            $echos = $echos . "Olangda=$Olangda " . "Olanggml=$Olanggml " . "Olangla=$Olangla ";
            $output = $output . ($Olangda ? " \$Olangda" : "")  . ($Olanggml ? " \$Olanggml" : "")  . ($Olangla ? " \$Olangla" : "") ;
            }
        if( hasArgument("Operiod") )
            {
            $Operiodc13 = existsArgumentWithValue("Operiod", "c13");
            $Operiodc20 = existsArgumentWithValue("Operiod", "c20");
            $Operiodc21 = existsArgumentWithValue("Operiod", "c21");
            $echos = $echos . "Operiodc13=$Operiodc13 " . "Operiodc20=$Operiodc20 " . "Operiodc21=$Operiodc21 ";
            $output = $output . ($Operiodc13 ? " \$Operiodc13" : "")  . ($Operiodc20 ? " \$Operiodc20" : "")  . ($Operiodc21 ? " \$Operiodc21" : "") ;
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
        if( hasArgument("Ofacetpos") )
            {
            $OfacetposDSL = existsArgumentWithValue("Ofacetpos", "DSL");
            $OfacetposHiNTS = existsArgumentWithValue("Ofacetpos", "HiNTS");
            $OfacetposUni = existsArgumentWithValue("Ofacetpos", "Uni");
            $echos = $echos . "OfacetposDSL=$OfacetposDSL " . "OfacetposHiNTS=$OfacetposHiNTS " . "OfacetposUni=$OfacetposUni ";
            $output = $output . ($OfacetposDSL ? " \$OfacetposDSL" : "")  . ($OfacetposHiNTS ? " \$OfacetposHiNTS" : "")  . ($OfacetposUni ? " \$OfacetposUni" : "") ;
            }

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $Laposfile = tempFileName("Lapos-results");
        $command = "echo $echos >> $Laposfile";
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
        if($mode == 'dry')
            {
            $Laposfile = tempFileName("Lapos-results");
            scripinit($inputF,$input,$output);
            }
        $toolres = "../texton-linguistic-resources";
        if($F != "")
            {
            if($mode == 'dry')
                {
                if($Olangla)
                    scrip("../bin/lapos -m $toolres/la/lapos < \$F");
                else if($Olangda)
                    {
                    if($Operiodc13)
                        scrip("../bin/lapos -m $toolres/da/lapos/c13 < \$F > \$Laposfile");
                    else if($Operiodc20)
                        scrip("../bin/lapos -m $toolres/da/lapos/c20 < \$F > \$Laposfile");
                    else if($Operiodc21)
                        scrip("../bin/lapos -m $toolres/da/lapos/c21 < \$F > \$Laposfile");
                    }
                else if($Olanggml)
                    {
                    scrip("../bin/lapos -m $toolres/gml/lapos/c13 < \$F > \$Laposfile");
                    }
                }
            else
                {
                logit("F $F");
                if($Olangla)
                    $command = "../bin/lapos -m $toolres/la/lapos < $F";
                else if($Olangda)
                    {
                    if($Operiodc13)
                        $command = "../bin/lapos -m $toolres/da/lapos/c13 < $F";
                    else if($Operiodc20)
                        $command = "../bin/lapos -m $toolres/da/lapos/c20 < $F";
                    else if($Operiodc21)
                        $command = "../bin/lapos -m $toolres/da/lapos/c21 < $F";
                    }
                else if($Olanggml)
                    {
                    $command = "../bin/lapos -m $toolres/gml/lapos/c13 < $F";
                    }
                logit($command);

                if($mode != 'dry')
                    $Laposfile = /*"laposfile";//*/ tempFileName("Lapos-results");
                if(($cmd = popen($command, "r")) == NULL)
                    {
                    throw new SystemExit(); // instead of exit()
                    }

                $tmpf = fopen($Laposfile,'w');
                while($read = fgets($cmd))
                    {
                    fwrite($tmpf, $read);
                    }
                fclose($tmpf);
                pclose($cmd);
                }
            }
        else
            { /*Code inspired by OpenNLPtagger service, uses stand off annotations. */
            if($mode == 'dry')
                {
                combine($IfacettokF,$IfacetsegF);

                if($Olangla)
                    scrip("../bin/lapos -m $toolres/la/lapos < \$plainfile > \$LaposfileRaw");
                else if($Olangda)
                    {
                    if($Operiodc13)
                        scrip("../bin/lapos -m $toolres/da/lapos/c13 < \$plainfile > \$LaposfileRaw");
                    else if($Operiodc20)
                        scrip("../bin/lapos -m $toolres/da/lapos/c20 < \$plainfile > \$LaposfileRaw");
                    else if($Operiodc21)
                        scrip("../bin/lapos -m $toolres/da/lapos/c21 < \$plainfile > \$LaposfileRaw");
                    }
                else if($Olanggml)
                    {
                    scrip("../bin/lapos -m $toolres/gml/lapos/c13 < \$plainfile > \$LaposfileRaw");
                    }

                postagannotation("\$IfacettokF","\$LaposfileRaw","\$plainfile");
                }
            else
                {
                logit("Lapos($IfacetsegF,$IfacettokF)");
                $plainfile = combine($IfacettokF,$IfacetsegF);
                $LaposfileRaw = tempFileName("Lapos-raw");

                logit("runit");
                logit("runit la $Olangla da $Olangda gml $Olanggml");
                $command = "NIKS";
                if($Olangla)
                    $command = "../bin/lapos -m $toolres/la/lapos < $plainfile";
                else if($Olangda)
                    {
                    if($Operiodc13)
                        $command = "../bin/lapos -m $toolres/da/lapos/c13 < $plainfile";
                    else if($Operiodc20)
                        $command = "../bin/lapos -m $toolres/da/lapos/c20 < $plainfile";
                    else if($Operiodc21)
                        $command = "../bin/lapos -m $toolres/da/lapos/c21 < $plainfile";
                    }
                else if($Olanggml)
                    {
                    $command = "../bin/lapos -m $toolres/gml/lapos/c13 < $plainfile";
                    }

                logit($command);

                if(($cmd = popen($command, "r")) == NULL)
                    {
                    throw new SystemExit(); // instead of exit()
                    }

                $tmpf = fopen($LaposfileRaw,'w');
                while($read = fgets($cmd))
                    {
                    logit($read);
                    fwrite($tmpf, $read);
                    }
                fclose($tmpf);
                pclose($cmd);

                $Laposfile = postagannotation($IfacettokF,$LaposfileRaw,$plainfile);
                logit('filename:'.$Laposfile);
                }
            }
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $Laposfile
//*/
        $tmpf = fopen($Laposfile,'r');

        if($tmpf)
            {
            //logit('output from Lapos:');
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

// START SPECIFIC CODE


    function combine($IfacettokF,$IfacetsegF)
        {
        global $mode;
        logit( "combine(" . $IfacettokF . "," . $IfacetsegF . ")\n");
        $plainfile = tempFileName("combine-tokseg-attribute");
        if($mode == 'dry')
            {
            scrip("../bin/bracmat '(inputTok=\"\$IfacettokF\") (inputSeg=\"\$IfacetsegF\") (output=\"\$plainfile\") (lowercase=\"yes\") (get\$\"../shared_scripts/tokseg2sent.bra\")'");
            }
        else
            {
            $command = "../bin/bracmat '(inputTok=\"$IfacettokF\") (inputSeg=\"$IfacetsegF\") (output=\"$plainfile\") (lowercase=\"yes\") (get\$\"../shared_scripts/tokseg2sent.bra\")'";
            logit($command);
            if(($cmd = popen($command, "r")) == NULL)
                exit(1);

            while($read = fgets($cmd))
                {
                }
            }
        return $plainfile;
        }

    function postagannotation($IfacettokF,$Laposfile,$uploadfileTokens)
        {
        global $mode;
        logit( "postagannotation(" . $IfacettokF . "," . $Laposfile . "," . $uploadfileTokens . ")\n");
        $posfile = tempFileName("postagannotation-posf-attribute");
        if($mode == 'dry')
            {
            scrip("../bin/bracmat '(inputTok=\"$IfacettokF\") (inputPos=\"$Laposfile\") (uploadfileTokens=\"$uploadfileTokens\") (output=\"\$Laposfile\") (get\$\"braposf.bra\")'");
            }
        else
            {
            $command = "../bin/bracmat '(inputTok=\"$IfacettokF\") (inputPos=\"$Laposfile\") (uploadfileTokens=\"$uploadfileTokens\") (output=\"$posfile\") (get\$\"braposf.bra\")'";
            logit($command);
            if(($cmd = popen($command, "r")) == NULL)
                exit(1);

            while($read = fgets($cmd))
                {
                }
            }
        return $posfile;
        }


// END SPECIFIC CODE

    loginit();
    do_Lapos();
    }
catch (SystemExit $e)
    {
    header('HTTP/1.0 404 An error occurred: ' . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }
?>

