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
ToolID         : RTFread
PassWord       :
Version        : 2.11.1
Title          : RTFreader
Path in URL    : rtfreader/	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : CST
ContentProvider: cst.ku.dk
Creator        : Bart Jongejan
InfoAbout      : None
Description    : Extracts segments from RTF-file or from plain text. Optionally tokenises. Keeps \f
ExternalURI    :
XMLparms       :
PostData       :
Inactive       :
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/RTFread.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */

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
    global $fscrip, $RTFreadfile;
    $fscrip = fopen($RTFreadfile,'w');
    if($fscrip)
        {
        fwrite($fscrip,"/*\n");
        fwrite($fscrip," * ToolID           : RTFread\n");
        fwrite($fscrip," * Version          : 2.11.1\n");
        fwrite($fscrip," * Title            : RTFreader\n");
        fwrite($fscrip," * ServiceURL       : http://localhost/rtfreader/\n");
        fwrite($fscrip," * Publisher        : CST\n");
        fwrite($fscrip," * ContentProvider  : cst.ku.dk\n");
        fwrite($fscrip," * Creator          : Bart Jongejan\n");
        fwrite($fscrip," * InfoAbout        : None\n");
        fwrite($fscrip," * Description      : Extracts segments from RTF-file or from plain text. Optionally tokenises. Keeps \\f\n");
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
    global $fscrip, $RTFreadfile;
    $fscrip = fopen($RTFreadfile,'a');
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
                $tempfilename = tempFileName("RTFread_{$requestParm}_");
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
/*
    function php_fix_raw_query()
        {
        //logit(" php_fix_raw_query");
        $post = '';

        // Try globals array
        if (!$post && isset($_GLOBALS) && isset($_GLOBALS["HTTP_RAW_POST_DATA"]))
            $post = $_GLOBALS["HTTP_RAW_POST_DATA"];
        //logit("A");
        // Try globals variable
        if (!$post)
            {
            $post = file_get_contents('php://input');
            if(!isset($post))
                $post = '';
            }
        //logit("B");
        // Try stream
        if (!$post)
            {
            if (!function_exists('file_get_contents'))
                {
                $fp = fopen("php://input", "r");
                if ($fp)
                    {
                    $post = '';

                    while (!feof($fp))
                        $post = fread($fp, 1024);

                    fclose($fp);
                    }
                }
            else
                {
                $post = "" . file_get_contents("php://input");
                }
            }
        //logit("C");
        $raw = !empty($_SERVER['QUERY_STRING']) ? sprintf('%s&%s', $_SERVER['QUERY_STRING'], $post) : $post;

        //logit("raw=".$raw);

        $arr = array();
        $pairs = explode('&', $raw);
        //logit("K");
        //logit("K");
        foreach($pairs as $i)
            {
            if(!empty($i))
                {
                list($name, $value) = explode('=', $i, 2);
                //logit("name=".$name." value=".$value);
                if (isset($arr[$name]) )
                    {
                    //logit("isset(" .$arr[$name].")");
                    if (is_array($arr[$name]) )
                        {
                        //logit("is_array(" .$name.")");
                        $arr[$name] = array_merge($arr[$name], array($value));
                        //logit("Merge: " . var_export($arr[$name], true));
                        }
                    else
                        {
                        $arr[$name] = array($arr[$name], $value);
                        }
                    }
                else
                    {
                    $arr[$name] = $value;
                    }
                }
            }
        $_REQUEST = $arr;
        # optionally return result array
            return $arr;
        }
 */

    function do_RTFread()
        {
        global $RTFreadfile;
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
        $mode = "";	/* If the value is 'dry', the wrapper is expected to return a script of what will be done if the value is not 'dry', but 'run'. */
        $inputF = "";	/* List of all input files. */
        $input = "";	/* List of all input features. */
        $output = "";	/* List of all output features. */
        $echos = "";	/* List arguments and their actual values. For sanity check of this generated script. All references to this variable can be removed once your web service is working as intended. */
        $F = "";	/* Input (ONLY used if there is exactly ONE input to this workflow step) */
        $Iappnrm = false;	/* Appearance in input is normalised (normaliseret) if true */
        $Iappocr = false;	/* Appearance in input is OCR if true */
        $Iappunn = false;	/* Appearance in input is unnormalised (ikke-normaliseret) if true */
        $Ifacetexc = false;	/* Type of content in input is text excerpts (Tekststumper) if true */
        $Ifacettok = false;	/* Type of content in input is tokens (tokens) if true */
        $Ifacettxt = false;	/* Type of content in input is text (ingen annotation) if true */
        $Iformatflat = false;	/* Format in input is plain (flad) if true */
        $Iformatrtf = false;	/* Format in input is RTF if true */
        $Ilangen = false;	/* Language in input is English (engelsk) if true */
        $Ipresnml = false;	/* Assemblage in input is normal if true */
        $Oappnrm = false;	/* Appearance in output is normalised (normaliseret) if true */
        $Oappunn = false;	/* Appearance in output is unnormalised (ikke-normaliseret) if true */
        $Ofacetpar = false;	/* Type of content in output is paragraphs (paragrafsegmenter) if true */
        $Ofacetseg = false;	/* Type of content in output is segments (sætningssegmenter) if true */
        $Ofacettok = false;	/* Type of content in output is tokens (tokens) if true */
        $Oformatflat = false;	/* Format in output is plain (flad) if true */
        $OformatplainD = false;	/* Format in output is plain text with ASCII 127 characters (flad tekst with ASCII 127 tegn) if true */
        $Olangen = false;	/* Language in output is English (engelsk) if true */
        $Opresnml = false;	/* Assemblage in output is normal if true */
        $IfacettokPT = false;	/* Style of type of content tokens (tokens) in input is Penn Treebank if true */
        $Ifacettoksimple = false;	/* Style of type of content tokens (tokens) in input is Simple if true */
        $OfacettokPT = false;	/* Style of type of content tokens (tokens) in output is Penn Treebank if true */
        $Ofacettoksimple = false;	/* Style of type of content tokens (tokens) in output is Simple if true */

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

/************************
* input/output features *
************************/
        if( hasArgument("Iapp") )
            {
            $Iappnrm = existsArgumentWithValue("Iapp", "nrm");
            $Iappocr = existsArgumentWithValue("Iapp", "ocr");
            $Iappunn = existsArgumentWithValue("Iapp", "unn");
            $echos = $echos . "Iappnrm=$Iappnrm " . "Iappocr=$Iappocr " . "Iappunn=$Iappunn ";
            $input = $input . ($Iappnrm ? " \$Iappnrm" : "")  . ($Iappocr ? " \$Iappocr" : "")  . ($Iappunn ? " \$Iappunn" : "") ;
            }
        if( hasArgument("Ifacet") )
            {
            $Ifacetexc = existsArgumentWithValue("Ifacet", "exc");
            $Ifacettok = existsArgumentWithValue("Ifacet", "tok");
            $Ifacettxt = existsArgumentWithValue("Ifacet", "txt");
            $echos = $echos . "Ifacetexc=$Ifacetexc " . "Ifacettok=$Ifacettok " . "Ifacettxt=$Ifacettxt ";
            $input = $input . ($Ifacetexc ? " \$Ifacetexc" : "")  . ($Ifacettok ? " \$Ifacettok" : "")  . ($Ifacettxt ? " \$Ifacettxt" : "") ;
            }
        if( hasArgument("Iformat") )
            {
            $Iformatflat = existsArgumentWithValue("Iformat", "flat");
            $Iformatrtf = existsArgumentWithValue("Iformat", "rtf");
            $echos = $echos . "Iformatflat=$Iformatflat " . "Iformatrtf=$Iformatrtf ";
            $input = $input . ($Iformatflat ? " \$Iformatflat" : "")  . ($Iformatrtf ? " \$Iformatrtf" : "") ;
            }
        if( hasArgument("Ilang") )
            {
            $Ilangen = existsArgumentWithValue("Ilang", "en");
            $echos = $echos . "Ilangen=$Ilangen ";
            $input = $input . ($Ilangen ? " \$Ilangen" : "") ;
            }
        if( hasArgument("Ipres") )
            {
            $Ipresnml = existsArgumentWithValue("Ipres", "nml");
            $echos = $echos . "Ipresnml=$Ipresnml ";
            $input = $input . ($Ipresnml ? " \$Ipresnml" : "") ;
            }
        if( hasArgument("Oapp") )
            {
            $Oappnrm = existsArgumentWithValue("Oapp", "nrm");
            $Oappunn = existsArgumentWithValue("Oapp", "unn");
            $echos = $echos . "Oappnrm=$Oappnrm " . "Oappunn=$Oappunn ";
            $output = $output . ($Oappnrm ? " \$Oappnrm" : "")  . ($Oappunn ? " \$Oappunn" : "") ;
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacetpar = existsArgumentWithValue("Ofacet", "par");
            $Ofacetseg = existsArgumentWithValue("Ofacet", "seg");
            $Ofacettok = existsArgumentWithValue("Ofacet", "tok");
            $echos = $echos . "Ofacetpar=$Ofacetpar " . "Ofacetseg=$Ofacetseg " . "Ofacettok=$Ofacettok ";
            $output = $output . ($Ofacetpar ? " \$Ofacetpar" : "")  . ($Ofacetseg ? " \$Ofacetseg" : "")  . ($Ofacettok ? " \$Ofacettok" : "") ;
            }
        if( hasArgument("Oformat") )
            {
            $Oformatflat = existsArgumentWithValue("Oformat", "flat");
            $OformatplainD = existsArgumentWithValue("Oformat", "plainD");
            $echos = $echos . "Oformatflat=$Oformatflat " . "OformatplainD=$OformatplainD ";
            $output = $output . ($Oformatflat ? " \$Oformatflat" : "")  . ($OformatplainD ? " \$OformatplainD" : "") ;
            }
        if( hasArgument("Olang") )
            {
            $Olangen = existsArgumentWithValue("Olang", "en");
            $echos = $echos . "Olangen=$Olangen ";
            $output = $output . ($Olangen ? " \$Olangen" : "") ;
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
        if( hasArgument("Ifacettok") )
            {
            $IfacettokPT = existsArgumentWithValue("Ifacettok", "PT");
            $Ifacettoksimple = existsArgumentWithValue("Ifacettok", "simple");
            $echos = $echos . "IfacettokPT=$IfacettokPT " . "Ifacettoksimple=$Ifacettoksimple ";
            $input = $input . ($IfacettokPT ? " \$IfacettokPT" : "")  . ($Ifacettoksimple ? " \$Ifacettoksimple" : "") ;
            }
        if( hasArgument("Ofacettok") )
            {
            $OfacettokPT = existsArgumentWithValue("Ofacettok", "PT");
            $Ofacettoksimple = existsArgumentWithValue("Ofacettok", "simple");
            $echos = $echos . "OfacettokPT=$OfacettokPT " . "Ofacettoksimple=$Ofacettoksimple ";
            $output = $output . ($OfacettokPT ? " \$OfacettokPT" : "")  . ($Ofacettoksimple ? " \$Ofacettoksimple" : "") ;
            }

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $RTFreadfile = tempFileName("RTFread-results");
        $command = "echo $echos >> $RTFreadfile";
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
   /*     $parms = php_fix_raw_query();
        ob_start();
        var_dump($_REQUEST);
        $dump = ob_get_clean();
        logit($dump);
        ob_start();
        var_dump($parms);
        $dump = ob_get_clean();
        logit($dump);
 */
        $RTFreadfile = tempFileName("RTFread-results");
        if($mode == 'dry')
            scripinit($inputF,$input,$output);

/*      $command = "echo $echos >> $RTFreadfile";
        logit($command);*/

        $tool = "../bin/rtfreader";
        $abbr = "";
        $res = "../texton-linguistic-resources";

        if($Iappocr)
            $nopt = " -n- ";
        else
            $nopt = " -n ";

        if( hasArgument("Ilang") )
            {
            $lang = getArgument("Ilang");
            logit("lang:" . $lang);
            switch($lang)
                {
                case "ast":
                case "ca":
                case "cy":
                case "ga":
                case "gd":
                case "gl":
                //case "gv": No abbreviations found for Manx
                    $abbr = "-a $res/$lang/tokeniser/$lang.dat ";
                    break;
                case "nb":
                    $abbr = "-a $res/no/tokeniser/abbr ";
                    break;
                case "bg":
                case "cs":
                case "da":
                case "de":
                case "el":
                case "en":
                case "es":
                case "et":
                case "fa":
                case "fr":
                case "hu":
                case "is":
                case "it":
                case "la":
                case "mk":
                case "nl":
                case "no":
                case "pl":
                case "pt":
                case "ro":
                case "ru":
                case "sk":
                case "sl":
                case "sr":
                case "sv":
                case "tr":
                case "uk":
                    $abbr = "-a $res/$lang/tokeniser/abbr ";
                    break;
                default:
                    $abbr = "";
                }
            }

        $tokentype = "-T- ";
        $DEL = "-D- ";

        if($Ofacettok && ($OformatplainD || !$Ifacettok))
            {
            if($Olangen)
                $tokentype = "-P ";
            else
                $tokentype = "-T ";
            if($OformatplainD)
                $DEL = "-D ";
            }

        if($mode == 'dry')
            {
            if($Ifacetexc)
                {
                $command = "sed -e 'G;G;G;' \$F > \$spacedfile";
                $command .= " && $tool -s $nopt -EUTF8 $tokentype -i \$spacedfile $abbr -t \$RTFreadfile $DEL";
                $command .= " && $tool $abbr -s -p $nopt -EUTF8 $tokentype -i \$spacedfile -t \$tokenisedfile $DEL";
                $command .= " && sed ':a;N;/\\n$/!s/\\n//;ta;P;d' \$tokenisedfile > \$RTFreadfile";
                $command .= " && curl -v -F job=$job -F name=\$RTFreadfile -F data=@\$RTFreadfile $post2  && rm \$RTFreadfile && rm \$F > ../log/rtfreader.log 2>&1 &";
                }
            else
                {
                $command = "$tool $nopt -EUTF8 -w- $tokentype -i \$F $abbr -t \$RTFreadfile $DEL";

                if($Ofacetpar)
                    {
                    $command .= " -p";
                    }

                $command .= " && curl -v -F job=$job -F name=\$RTFreadfile -F data=@\$RTFreadfile $post2  && rm \$RTFreadfile && rm \$F > ../log/rtfreader.log 2>&1 &";
                }

            scrip($command);

            $tmpf = fopen($RTFreadfile,'r');

            if($tmpf)
                {
                while($line = fgets($tmpf))
                    {
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
            if($Ifacetexc)
                {
                $spacedfile = tempFileName("RTFread-spaced");
                $tokenisedfile = tempFileName("RTFread-tokenised");
                $command = "sed -e 'G;G;G;' $F > $spacedfile";
                $command .= " && $tool -s $nopt -EUTF8 $tokentype -i $spacedfile $abbr -t $RTFreadfile $DEL";

                $command .= " && $tool $abbr -s -p $nopt -EUTF8 $tokentype -i $spacedfile -t $tokenisedfile $DEL";
                $command .= " && sed ':a;N;/\\n$/!s/\\n//;ta;P;d' $tokenisedfile > $RTFreadfile";
                $command .= " && curl -v -F job=$job -F name=$RTFreadfile -F data=@$RTFreadfile $post2  && rm $RTFreadfile && rm $F > ../log/rtfreader.log 2>&1 &";
                }
            else
                {
                $command = "$tool $nopt -EUTF8 -w- $tokentype -i $F $abbr -t $RTFreadfile $DEL";

                if($Ofacetpar)
                    {
                    $command .= " -p";
                    }

                $command .= " && curl -v -F job=$job -F name=$RTFreadfile -F data=@$RTFreadfile $post2  && rm $RTFreadfile && rm $F > ../log/rtfreader.log 2>&1 &";
                }
            logit($command);
            exec($command);
            logit('RETURN 202');
            header("HTTP/1.0 202 Accepted");
            }
        }
    loginit();
    do_RTFread();
    }
catch (SystemExit $e)
    {
    header('HTTP/1.0 404 An error occurred: ' . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }
?>

