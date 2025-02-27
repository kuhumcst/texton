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
ToolID         : mate-parser
PassWord       : 
Version        : 3.3
Title          : Bohnet parser
Path in URL    : mate-parser	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : mate-tools
ContentProvider: mate-tools
Creator        : Bernd Bohnet
InfoAbout      : http://code.google.com/p/mate-tools/
Description    : Dependency parser, part of mate-tools.
ExternalURI    : http://barbar.cs.lth.se:8081/
RestAPIkey         : *****
RestAPIpassword    : *****
MultiInp       : 
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/mateParser.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */

/*  TODO Set $dodelete to false if temporary files in /tmp should not be deleted before returning. */
$dodelete = true;
$tobedeleted = array();

function loginit()  /* Wipes the contents of the log file! TODO Change this behaviour if needed. */
    {
//    return;
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
//    return;
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
    global $fscrip, $mateParserfile;
    $fscrip = fopen($mateParserfile,'w');
    if($fscrip)
        {
        fwrite($fscrip,"/*\n");
        fwrite($fscrip," * ToolID           : mate-parser\n");
        fwrite($fscrip," * Version          : 3.3\n");
        fwrite($fscrip," * Title            : Bohnet parser\n");
        fwrite($fscrip," * ServiceURL       : http://localhost/mate-parser\n");
        fwrite($fscrip," * Publisher        : mate-tools\n");
        fwrite($fscrip," * ContentProvider  : mate-tools\n");
        fwrite($fscrip," * Creator          : Bernd Bohnet\n");
        fwrite($fscrip," * InfoAbout        : http://code.google.com/p/mate-tools/\n");
        fwrite($fscrip," * Description      : Dependency parser, part of mate-tools.\n");
        fwrite($fscrip," * ExternalURI      : http://barbar.cs.lth.se:8081/\n");
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
    global $fscrip, $mateParserfile;
    $fscrip = fopen($mateParserfile,'a');
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
                $tempfilename = tempFileName("mateParser_{$requestParm}_");
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

    function do_mateParser()
        {
        global $mateParserfile;
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
        $IfacetlemF = "";	/* Input with type of content lemmas (lemmaer) */
        $IfacetposF = "";	/* Input with type of content PoS-tags (PoS-tags) */
        $IfacetsegF = "";	/* Input with type of content segments (sætningssegmenter) */
        $IfacettokF = "";	/* Input with type of content tokens (tokens) */
        $Iambiguna = false;	/* Ambiguity in input is unambiguous (utvetydig) if true */
        $Ifacetlem = false;	/* Type of content in input is lemmas (lemmaer) if true */
        $Ifacetpos = false;	/* Type of content in input is PoS-tags (PoS-tags) if true */
        $Ifacetseg = false;	/* Type of content in input is segments (sætningssegmenter) if true */
        $Ifacettok = false;	/* Type of content in input is tokens (tokens) if true */
        $Iformatteip5 = false;	/* Format in input is TEIP5 if true */
        $Ilangda = false;	/* Language in input is Danish (dansk) if true */
        $Ilangde = false;	/* Language in input is German (tysk) if true */
        $Ilanges = false;	/* Language in input is Spanish (spansk) if true */
        $Ilangfr = false;	/* Language in input is French (fransk) if true */
        $Iperiodc21 = false;	/* Historical period in input is contemporary (efterkrigstiden) if true */
        $Ipressof = false;	/* Assemblage in input is standoff annotations if true */
        $Oambiguna = false;	/* Ambiguity in output is unambiguous (utvetydig) if true */
        $Ofacetlem = false;	/* Type of content in output is lemmas (lemmaer) if true */
        $Ofacetpos = false;	/* Type of content in output is PoS-tags (PoS-tags) if true */
        $Ofacetseg = false;	/* Type of content in output is segments (sætningssegmenter) if true */
        $Ofacetstx = false;	/* Type of content in output is syntax (dependency structure) (syntaks (dependensstruktur)) if true */
        $Ofacettok = false;	/* Type of content in output is tokens (tokens) if true */
        $Oformatconll = false;	/* Format in output is CoNLL if true */
        $Olangda = false;	/* Language in output is Danish (dansk) if true */
        $Olangde = false;	/* Language in output is German (tysk) if true */
        $Olanges = false;	/* Language in output is Spanish (spansk) if true */
        $Olangfr = false;	/* Language in output is French (fransk) if true */
        $Operiodc21 = false;	/* Historical period in output is contemporary (efterkrigstiden) if true */
        $Opresnml = false;	/* Assemblage in output is normal if true */
        $IfacetposUni = false;	/* Style of type of content PoS-tags (PoS-tags) in input is Universal Part-of-Speech Tagset if true */
        $OfacetposUni = false;	/* Style of type of content PoS-tags (PoS-tags) in output is Universal Part-of-Speech Tagset if true */
        $Oformatconllcnl2009 = false;	/* Style of format CoNLL in output is CoNLL 2009 (14 columns)CoNLL 2009 (14 kolonner) if true */

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
                header("HTTP/1.0 404 Input with type of content 'lemmas (lemmaer)' not found (IfacetlemF parameter). ");
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
                header("HTTP/1.0 404 Input with type of content 'PoS-tags (PoS-tags)' not found (IfacetposF parameter). ");
                return;
                }
            $echos = $echos . "IfacetposF=$IfacetposF ";
            $inputF = $inputF . " \$IfacetposF ";
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
            $Ifacetlem = existsArgumentWithValue("Ifacet", "lem");
            $Ifacetpos = existsArgumentWithValue("Ifacet", "pos");
            $Ifacetseg = existsArgumentWithValue("Ifacet", "seg");
            $Ifacettok = existsArgumentWithValue("Ifacet", "tok");
            $echos = $echos . "Ifacetlem=$Ifacetlem " . "Ifacetpos=$Ifacetpos " . "Ifacetseg=$Ifacetseg " . "Ifacettok=$Ifacettok ";
            $input = $input . ($Ifacetlem ? " \$Ifacetlem" : "")  . ($Ifacetpos ? " \$Ifacetpos" : "")  . ($Ifacetseg ? " \$Ifacetseg" : "")  . ($Ifacettok ? " \$Ifacettok" : "") ;
            }
        if( hasArgument("Iformat") )
            {
            $Iformatteip5 = existsArgumentWithValue("Iformat", "teip5");
            $echos = $echos . "Iformatteip5=$Iformatteip5 ";
            $input = $input . ($Iformatteip5 ? " \$Iformatteip5" : "") ;
            }
        if( hasArgument("Ilang") )
            {
            $Ilangda = existsArgumentWithValue("Ilang", "da");
            $Ilangde = existsArgumentWithValue("Ilang", "de");
            $Ilanges = existsArgumentWithValue("Ilang", "es");
            $Ilangfr = existsArgumentWithValue("Ilang", "fr");
            $echos = $echos . "Ilangda=$Ilangda " . "Ilangde=$Ilangde " . "Ilanges=$Ilanges " . "Ilangfr=$Ilangfr ";
            $input = $input . ($Ilangda ? " \$Ilangda" : "")  . ($Ilangde ? " \$Ilangde" : "")  . ($Ilanges ? " \$Ilanges" : "")  . ($Ilangfr ? " \$Ilangfr" : "") ;
            }
        if( hasArgument("Iperiod") )
            {
            $Iperiodc21 = existsArgumentWithValue("Iperiod", "c21");
            $echos = $echos . "Iperiodc21=$Iperiodc21 ";
            $input = $input . ($Iperiodc21 ? " \$Iperiodc21" : "") ;
            }
        if( hasArgument("Ipres") )
            {
            $Ipressof = existsArgumentWithValue("Ipres", "sof");
            $echos = $echos . "Ipressof=$Ipressof ";
            $input = $input . ($Ipressof ? " \$Ipressof" : "") ;
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
            $Ofacetpos = existsArgumentWithValue("Ofacet", "pos");
            $Ofacetseg = existsArgumentWithValue("Ofacet", "seg");
            $Ofacetstx = existsArgumentWithValue("Ofacet", "stx");
            $Ofacettok = existsArgumentWithValue("Ofacet", "tok");
            $echos = $echos . "Ofacetlem=$Ofacetlem " . "Ofacetpos=$Ofacetpos " . "Ofacetseg=$Ofacetseg " . "Ofacetstx=$Ofacetstx " . "Ofacettok=$Ofacettok ";
            $output = $output . ($Ofacetlem ? " \$Ofacetlem" : "")  . ($Ofacetpos ? " \$Ofacetpos" : "")  . ($Ofacetseg ? " \$Ofacetseg" : "")  . ($Ofacetstx ? " \$Ofacetstx" : "")  . ($Ofacettok ? " \$Ofacettok" : "") ;
            }
        if( hasArgument("Oformat") )
            {
            $Oformatconll = existsArgumentWithValue("Oformat", "conll");
            $echos = $echos . "Oformatconll=$Oformatconll ";
            $output = $output . ($Oformatconll ? " \$Oformatconll" : "") ;
            }
        if( hasArgument("Olang") )
            {
            $Olangda = existsArgumentWithValue("Olang", "da");
            $Olangde = existsArgumentWithValue("Olang", "de");
            $Olanges = existsArgumentWithValue("Olang", "es");
            $Olangfr = existsArgumentWithValue("Olang", "fr");
            $echos = $echos . "Olangda=$Olangda " . "Olangde=$Olangde " . "Olanges=$Olanges " . "Olangfr=$Olangfr ";
            $output = $output . ($Olangda ? " \$Olangda" : "")  . ($Olangde ? " \$Olangde" : "")  . ($Olanges ? " \$Olanges" : "")  . ($Olangfr ? " \$Olangfr" : "") ;
            }
        if( hasArgument("Operiod") )
            {
            $Operiodc21 = existsArgumentWithValue("Operiod", "c21");
            $echos = $echos . "Operiodc21=$Operiodc21 ";
            $output = $output . ($Operiodc21 ? " \$Operiodc21" : "") ;
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
        if( hasArgument("Ifacetpos") )
            {
            $IfacetposUni = existsArgumentWithValue("Ifacetpos", "Uni");
            $echos = $echos . "IfacetposUni=$IfacetposUni ";
            $input = $input . ($IfacetposUni ? " \$IfacetposUni" : "") ;
            }
        if( hasArgument("Ofacetpos") )
            {
            $OfacetposUni = existsArgumentWithValue("Ofacetpos", "Uni");
            $echos = $echos . "OfacetposUni=$OfacetposUni ";
            $output = $output . ($OfacetposUni ? " \$OfacetposUni" : "") ;
            }
        if( hasArgument("Oformatconll") )
            {
            $Oformatconllcnl2009 = existsArgumentWithValue("Oformatconll", "cnl2009");
            $echos = $echos . "Oformatconllcnl2009=$Oformatconllcnl2009 ";
            $output = $output . ($Oformatconllcnl2009 ? " \$Oformatconllcnl2009" : "") ;
            }

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $mateParserfile = tempFileName("mateParser-results");
        $command = "echo $echos >> $mateParserfile";
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
        $mateParserfile = tempFileName("mateParser-raw");
        if($mode == 'dry')
            scripinit($inputF,$input,$output);

        if($IfacetsegF != '' && $IfacettokF != '' && $IfacetposF != '')
            {
            logit("NOW conllout");

            if($mode == 'dry')
                conllout("\$IfacettokF","\$IfacetsegF","\$IfacetposF",$IfacetlemF);
            else
                $conll = conllout($IfacettokF,$IfacetsegF,$IfacetposF,$IfacetlemF);

            logit("conllout DONE: $conll ");

            $lang = "da";
            $res = "../texton-linguistic-resources";

            if($Ilangda)
                $lang = "$res/da/BohnetsParser/ddt-universal.parse";
            else if($Ilangde)
                $lang = "$res/de/BohnetsParser/parser-ger-3.6.model";
//            else if($Ilangen)
  //              $lang = "$res/en/BohnetsParser/CoNLL2009-ST-English-ALL.anna-3.3.parser.model";
            else if($Ilanges)
                $lang = "$res/es/BohnetsParser/CoNLL2009-ST-Spanish-ALL.anna-3.3.parser.model";
            else if($Ilangfr)
                $lang = "$res/fr/BohnetsParser/ftb6_1.conll09.crossannotated.anna-3.3-d8.jar.parser.model";

            if($mode == 'dry')
            {
                scrip("/* CURL http://localhost:8080/BohnetsParser/ for each line in \$conllfile. See index.php */");
                scrip("repeatHttp(\$conllfile,\$mateParserfile," . realpath($lang) . ")");
            }
            else
                repeatHttp($conll,$mateParserfile,realpath($lang));

            logit("mateparser DONE: $mateParserfile ");
            }
        else
            {
            header("HTTP/1.0 404 Input not found (IfacetsegF, IfacettokF and IfacetposF). ");
            return;
            }
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $mateParserfile
//*/
        $tmpf = fopen($mateParserfile,'r');

        if($tmpf)
            {
            //logit('output from mateParser:');
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

    function http($input,$output,$lang)
        {
        logit("http(".$input.",".$output.",".$lang.")");
        $CF = curl_file_create($input, 'text/plain', $input);
        //$CF = new CURLFile($input);
        //$postFileName = tempFileName('bohPost');
        $postFileName = 'bohPost';

        $CF->setPostFilename($postFileName);
        $postfields = array(
            'model' => $lang,
            'inputFile' => $CF,
            );
        $ch = curl_init("http://localhost:8080/BohnetsParser/");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // stop verifying certificate
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true); // enable posting
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // if any redirection after upload
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        $fp = fopen($output, "w");
        curl_setopt($ch, CURLOPT_FILE, $fp);
        //curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HEADER, "Content-Type: text/plain; charset=UTF-8");
        $r = utf8_decode(curl_exec($ch));
        curl_close($ch);
        fclose($fp);
        }

    function del($name)
        {
        global $tobedeleted;
        global $dodelete;
        if($dodelete)
            $tobedeleted[$name] = true;
        }
    function repeatHttp($input,$output,$lang)
        {
        $ftemp = fopen($output,'w');
        if($ftemp)
            {
            $lines = file($input);
            $inn = tempFileName('bohnet');
            $inp = fopen($inn,"w");
            if($inp)
                {
                $outn = tempFileName("bohnetout");
                $sentenceCounter = 0;
                $blocksize = 100;
                foreach ($lines as $k => $v)
                    {
                    fwrite($inp,$lines[$k]);
                    if (!trim($v))
                        {
                        $sentenceCounter = $sentenceCounter + 1;
                        if($sentenceCounter == $blocksize)
                            {
                            $sentenceCounter = 0;
                            fclose($inp);
                            http($inn,$outn,$lang);
                            del($inn);
                            $lins = file($outn);
                            foreach($lins as $kk => $vv)
                                {
                                fwrite($ftemp,$lins[$kk]);
                                }
                            del($outn);
                            $inn = tempFileName("bohnet");
                            $inp = fopen($inn,"w");
                            $outn = tempFileName("bohnetout");
                            }
                        }
                    }
                if($sentenceCounter < $blocksize)
                    {
                    $sentenceCounter = 0;
                    fclose($inp);
                    http($inn,$outn,$lang);
                    del($inn);
                    $lins = file($outn);
                    foreach($lins as $kk => $vv)
                        {
                        fwrite($ftemp,$lins[$kk]);
                        }
                    del($outn);
                    $inn = tempFileName("bohnet");
                    $inp = fopen($inn,"w");
                    $outn = tempFileName("bohnetout");
                    }

                fclose($inp);
                http($inn,$outn,$lang);
                del($inn);
                $lins = file($outn);
                foreach($lins as $kk => $vv)
                    {
                    fwrite($ftemp,$lins[$kk]);
                    }
                del($outn);
                }
            fclose($ftemp);
            }
        }

    function conllout($IfacettokF,$IfacetsegF,$IfacetposF,$IfacetlemF)
        {
        global $mode;
        logit("conllout($IfacettokF,$IfacetsegF,$IfacetposF,$IfacetlemF)");

        $conllfile = tempFileName("conllout-results");
        if($mode == 'dry')
        {
            if($IfacetlemF == "")
                scrip("../bin/bracmat 'get\$\"../shared_scripts/conlln.bra\"' '\$conllfile' '$IfacettokF' '$IfacetsegF' '$IfacetposF' ''");
            else
                scrip("../bin/bracmat 'get\$\"../shared_scripts/conlln.bra\"' '\$conllfile' '$IfacettokF' '$IfacetsegF' '$IfacetposF' '\$IfacetlemF'");
        }
        else
        {
            $command = "../bin/bracmat 'get\$\"../shared_scripts/conlln.bra\"' '$conllfile' '$IfacettokF' '$IfacetsegF' '$IfacetposF' '$IfacetlemF'";

            logit($command);

            if(($cmd = popen($command, "r")) == NULL)
            {
                throw new SystemExit(); // instead of exit()
            }

            while($read = fgets($cmd))
            {
            }

            pclose($cmd);
        }
        return $conllfile;
        }

    loginit();
    do_mateParser();
    }
catch (SystemExit $e)
    {
    header('HTTP/1.0 404 An error occurred: ' . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }
?>

