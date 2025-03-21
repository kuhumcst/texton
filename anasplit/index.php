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
ToolID         : anasplit
PassWord       : 
Version        : 0.1
Title          : Anno-splitter
Path in URL    : anasplit	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : CST
ContentProvider: CST
Creator        : Bart Jongejan
InfoAbout      : -
Description    : Takes TEI P5 document containing multiple stand-off annotation groups (spanGrp). Outputs one of the annotation groups.
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
$toollog = '../log/anasplit.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */

/*  TODO Set $dodelete to false if temporary files in /tmp should not be deleted before returning. */
$dodelete = true;
$tobedeleted = array();

function loginit()  /* Wipes the contents of the log file! TODO Change this behaviour if needed. */
    {
    //return;
    global $toollog,$ftemp;
    $ftemp = fopen($toollog,'a');
//    $ftemp = fopen($toollog,'w');
    if($ftemp)
        {
        fwrite($ftemp,$toollog . "\nLOGINIT\n");
        fclose($ftemp);
        }
    }

function logit($str) /* TODO You can use this function to write strings to the log file. */
    {
  //  return;
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
    global $fscrip, $anasplitfile;
    $fscrip = fopen($anasplitfile,'w');
    if($fscrip)
        {
        fwrite($fscrip,"/*\n");
        fwrite($fscrip," * ToolID           : anasplit\n");
        fwrite($fscrip," * Version          : 0.1\n");
        fwrite($fscrip," * Title            : Anno-splitter\n");
        fwrite($fscrip," * ServiceURL       : http://localhost/anasplit\n");
        fwrite($fscrip," * Publisher        : CST\n");
        fwrite($fscrip," * ContentProvider  : CST\n");
        fwrite($fscrip," * Creator          : Bart Jongejan\n");
        fwrite($fscrip," * InfoAbout        : -\n");
        fwrite($fscrip," * Description      : Takes TEI P5 document containing multiple stand-off annotation groups (spanGrp). Outputs one of the annotation groups.\n");
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
    global $fscrip, $anasplitfile;
    $fscrip = fopen($anasplitfile,'a');
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
                $tempfilename = tempFileName("anasplit_{$requestParm}_");
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

    function do_anasplit()
        {
        global $anasplitfile;
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
        $Iappdrty = false;	/* Appearance in input is optimized for software (bedst for programmer) if true */
        $Iappnrm = false;	/* Appearance in input is normalised (normaliseret) if true */
        $Iappunn = false;	/* Appearance in input is unnormalised (ikke-normaliseret) if true */
        $Ifacet_etc_lem_ner_pos_seg_snt_stc_stx_tok = false;	/* Type of content in input is structured text (struktureret tekst) and lemmas (lemmaer) and name entities (navne) and PoS-tags (PoS-tags) and segments (sætningssegmenter) and sentiment and syntax (constituency relations) (syntaks (frasestruktur)) and syntax (dependency structure) (syntaks (dependensstruktur)) and tokens (tokens) if true */
        $Ifacet_etc_lem_ner_pos_seg_snt_stc_tok = false;	/* Type of content in input is structured text (struktureret tekst) and lemmas (lemmaer) and name entities (navne) and PoS-tags (PoS-tags) and segments (sætningssegmenter) and sentiment and syntax (constituency relations) (syntaks (frasestruktur)) and tokens (tokens) if true */
        $Ifacet_etc_lem_ner_pos_seg_snt_stx_tok = false;	/* Type of content in input is structured text (struktureret tekst) and lemmas (lemmaer) and name entities (navne) and PoS-tags (PoS-tags) and segments (sætningssegmenter) and sentiment and syntax (dependency structure) (syntaks (dependensstruktur)) and tokens (tokens) if true */
        $Ifacet_etc_ner_pos_seg_sent_stc_tok = false;	/* Type of content in input is structured text (struktureret tekst) and name entities (navne) and PoS-tags (PoS-tags) and segments (sætningssegmenter) and sentences (sætninger, før tokenisering) and syntax (constituency relations) (syntaks (frasestruktur)) and tokens (tokens) if true */
        $Ifacet_etc_ner_pos_seg_sent_stx_tok = false;	/* Type of content in input is structured text (struktureret tekst) and name entities (navne) and PoS-tags (PoS-tags) and segments (sætningssegmenter) and sentences (sætninger, før tokenisering) and syntax (dependency structure) (syntaks (dependensstruktur)) and tokens (tokens) if true */
        $Ifacet_etc_pos_seg_sent_stc_tok = false;	/* Type of content in input is structured text (struktureret tekst) and PoS-tags (PoS-tags) and segments (sætningssegmenter) and sentences (sætninger, før tokenisering) and syntax (constituency relations) (syntaks (frasestruktur)) and tokens (tokens) if true */
        $Ifacet_lem_mrf_pos_stx = false;	/* Type of content in input is lemmas (lemmaer) and morphological features (morfologiske træk) and PoS-tags (PoS-tags) and syntax (dependency structure) (syntaks (dependensstruktur)) if true */
        $Ifacet_mrf_pos = false;	/* Type of content in input is morphological features (morfologiske træk) and PoS-tags (PoS-tags) if true */
        $Ifacet_ner_pos_stx = false;	/* Type of content in input is name entities (navne) and PoS-tags (PoS-tags) and syntax (dependency structure) (syntaks (dependensstruktur)) if true */
        $Iformatteip5 = false;	/* Format in input is TEIP5 if true */
        $Ipressof = false;	/* Assemblage in input is standoff annotations if true */
        $Oappprtty = false;	/* Appearance in output is pretty printed (nydelig opsætning) if true */
        $Ofacetetc = false;	/* Type of content in output is structured text (struktureret tekst) if true */
        $Ofacetlem = false;	/* Type of content in output is lemmas (lemmaer) if true */
        $Ofacetmrf = false;	/* Type of content in output is morphological features (morfologiske træk) if true */
        $Ofacetner = false;	/* Type of content in output is name entities (navne) if true */
        $Ofacetpos = false;	/* Type of content in output is PoS-tags (PoS-tags) if true */
        $Ofacetseg = false;	/* Type of content in output is segments (sætningssegmenter) if true */
        $Ofacetsent = false;	/* Type of content in output is sentences (sætninger, før tokenisering) if true */
        $Ofacetsnt = false;	/* Type of content in output is sentiment if true */
        $Ofacetstc = false;	/* Type of content in output is syntax (constituency relations) (syntaks (frasestruktur)) if true */
        $Ofacetstx = false;	/* Type of content in output is syntax (dependency structure) (syntaks (dependensstruktur)) if true */
        $Ofacettok = false;	/* Type of content in output is tokens (tokens) if true */
        $Oformatpt = false;	/* Format in output is Penn Treebank if true */
        $Oformatteip5 = false;	/* Format in output is TEIP5 if true */
        $Opressof = false;	/* Assemblage in output is standoff annotations if true */

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
        if( hasArgument("Iapp") )
            {
            $Iappdrty = existsArgumentWithValue("Iapp", "drty");
            $Iappnrm = existsArgumentWithValue("Iapp", "nrm");
            $Iappunn = existsArgumentWithValue("Iapp", "unn");
            $echos = $echos . "Iappdrty=$Iappdrty " . "Iappnrm=$Iappnrm " . "Iappunn=$Iappunn ";
            $input = $input . ($Iappdrty ? " \$Iappdrty" : "")  . ($Iappnrm ? " \$Iappnrm" : "")  . ($Iappunn ? " \$Iappunn" : "") ;
            }
        if( hasArgument("Ifacet") )
            {
            $Ifacet_etc_lem_ner_pos_seg_snt_stc_stx_tok = existsArgumentWithValue("Ifacet", "_etc_lem_ner_pos_seg_snt_stc_stx_tok");
            $Ifacet_etc_lem_ner_pos_seg_snt_stc_tok = existsArgumentWithValue("Ifacet", "_etc_lem_ner_pos_seg_snt_stc_tok");
            $Ifacet_etc_lem_ner_pos_seg_snt_stx_tok = existsArgumentWithValue("Ifacet", "_etc_lem_ner_pos_seg_snt_stx_tok");
            $Ifacet_etc_ner_pos_seg_sent_stc_tok = existsArgumentWithValue("Ifacet", "_etc_ner_pos_seg_sent_stc_tok");
            $Ifacet_etc_ner_pos_seg_sent_stx_tok = existsArgumentWithValue("Ifacet", "_etc_ner_pos_seg_sent_stx_tok");
            $Ifacet_etc_pos_seg_sent_stc_tok = existsArgumentWithValue("Ifacet", "_etc_pos_seg_sent_stc_tok");
            $Ifacet_lem_mrf_pos_stx = existsArgumentWithValue("Ifacet", "_lem_mrf_pos_stx");
            $Ifacet_mrf_pos = existsArgumentWithValue("Ifacet", "_mrf_pos");
            $Ifacet_ner_pos_stx = existsArgumentWithValue("Ifacet", "_ner_pos_stx");
            $echos = $echos . "Ifacet_etc_lem_ner_pos_seg_snt_stc_stx_tok=$Ifacet_etc_lem_ner_pos_seg_snt_stc_stx_tok " . "Ifacet_etc_lem_ner_pos_seg_snt_stc_tok=$Ifacet_etc_lem_ner_pos_seg_snt_stc_tok " . "Ifacet_etc_lem_ner_pos_seg_snt_stx_tok=$Ifacet_etc_lem_ner_pos_seg_snt_stx_tok " . "Ifacet_etc_ner_pos_seg_sent_stc_tok=$Ifacet_etc_ner_pos_seg_sent_stc_tok " . "Ifacet_etc_ner_pos_seg_sent_stx_tok=$Ifacet_etc_ner_pos_seg_sent_stx_tok " . "Ifacet_etc_pos_seg_sent_stc_tok=$Ifacet_etc_pos_seg_sent_stc_tok " . "Ifacet_lem_mrf_pos_stx=$Ifacet_lem_mrf_pos_stx " . "Ifacet_mrf_pos=$Ifacet_mrf_pos " . "Ifacet_ner_pos_stx=$Ifacet_ner_pos_stx ";
            $input = $input . ($Ifacet_etc_lem_ner_pos_seg_snt_stc_stx_tok ? " \$Ifacet_etc_lem_ner_pos_seg_snt_stc_stx_tok" : "")  . ($Ifacet_etc_lem_ner_pos_seg_snt_stc_tok ? " \$Ifacet_etc_lem_ner_pos_seg_snt_stc_tok" : "")  . ($Ifacet_etc_lem_ner_pos_seg_snt_stx_tok ? " \$Ifacet_etc_lem_ner_pos_seg_snt_stx_tok" : "")  . ($Ifacet_etc_ner_pos_seg_sent_stc_tok ? " \$Ifacet_etc_ner_pos_seg_sent_stc_tok" : "")  . ($Ifacet_etc_ner_pos_seg_sent_stx_tok ? " \$Ifacet_etc_ner_pos_seg_sent_stx_tok" : "")  . ($Ifacet_etc_pos_seg_sent_stc_tok ? " \$Ifacet_etc_pos_seg_sent_stc_tok" : "")  . ($Ifacet_lem_mrf_pos_stx ? " \$Ifacet_lem_mrf_pos_stx" : "")  . ($Ifacet_mrf_pos ? " \$Ifacet_mrf_pos" : "")  . ($Ifacet_ner_pos_stx ? " \$Ifacet_ner_pos_stx" : "") ;
            }
        if( hasArgument("Iformat") )
            {
            $Iformatteip5 = existsArgumentWithValue("Iformat", "teip5");
            $echos = $echos . "Iformatteip5=$Iformatteip5 ";
            $input = $input . ($Iformatteip5 ? " \$Iformatteip5" : "") ;
            }
        if( hasArgument("Ipres") )
            {
            $Ipressof = existsArgumentWithValue("Ipres", "sof");
            $echos = $echos . "Ipressof=$Ipressof ";
            $input = $input . ($Ipressof ? " \$Ipressof" : "") ;
            }
        if( hasArgument("Oapp") )
            {
            $Oappprtty = existsArgumentWithValue("Oapp", "prtty");
            $echos = $echos . "Oappprtty=$Oappprtty ";
            $output = $output . ($Oappprtty ? " \$Oappprtty" : "") ;
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacetetc = existsArgumentWithValue("Ofacet", "etc");
            $Ofacetlem = existsArgumentWithValue("Ofacet", "lem");
            $Ofacetmrf = existsArgumentWithValue("Ofacet", "mrf");
            $Ofacetner = existsArgumentWithValue("Ofacet", "ner");
            $Ofacetpos = existsArgumentWithValue("Ofacet", "pos");
            $Ofacetseg = existsArgumentWithValue("Ofacet", "seg");
            $Ofacetsent = existsArgumentWithValue("Ofacet", "sent");
            $Ofacetsnt = existsArgumentWithValue("Ofacet", "snt");
            $Ofacetstc = existsArgumentWithValue("Ofacet", "stc");
            $Ofacetstx = existsArgumentWithValue("Ofacet", "stx");
            $Ofacettok = existsArgumentWithValue("Ofacet", "tok");
            $echos = $echos . "Ofacetetc=$Ofacetetc " . "Ofacetlem=$Ofacetlem " . "Ofacetmrf=$Ofacetmrf " . "Ofacetner=$Ofacetner " . "Ofacetpos=$Ofacetpos " . "Ofacetseg=$Ofacetseg " . "Ofacetsent=$Ofacetsent " . "Ofacetsnt=$Ofacetsnt " . "Ofacetstc=$Ofacetstc " . "Ofacetstx=$Ofacetstx " . "Ofacettok=$Ofacettok ";
            $output = $output . ($Ofacetetc ? " \$Ofacetetc" : "")  . ($Ofacetlem ? " \$Ofacetlem" : "")  . ($Ofacetmrf ? " \$Ofacetmrf" : "")  . ($Ofacetner ? " \$Ofacetner" : "")  . ($Ofacetpos ? " \$Ofacetpos" : "")  . ($Ofacetseg ? " \$Ofacetseg" : "")  . ($Ofacetsent ? " \$Ofacetsent" : "")  . ($Ofacetsnt ? " \$Ofacetsnt" : "")  . ($Ofacetstc ? " \$Ofacetstc" : "")  . ($Ofacetstx ? " \$Ofacetstx" : "")  . ($Ofacettok ? " \$Ofacettok" : "") ;
            }
        if( hasArgument("Oformat") )
            {
            $Oformatpt = existsArgumentWithValue("Oformat", "pt");
            $Oformatteip5 = existsArgumentWithValue("Oformat", "teip5");
            $echos = $echos . "Oformatpt=$Oformatpt " . "Oformatteip5=$Oformatteip5 ";
            $output = $output . ($Oformatpt ? " \$Oformatpt" : "")  . ($Oformatteip5 ? " \$Oformatteip5" : "") ;
            }
        if( hasArgument("Opres") )
            {
            $Opressof = existsArgumentWithValue("Opres", "sof");
            $echos = $echos . "Opressof=$Opressof ";
            $output = $output . ($Opressof ? " \$Opressof" : "") ;
            }

/*******************************
* input/output features styles *
*******************************/

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $anasplitfile = tempFileName("anasplit-results");
        $command = "echo $echos >> $anasplitfile";
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
        $anasplitfile = tempFileName("anasplit-results");
        if($mode === 'dry')
            scripinit($inputF,$input,$output);
        if(hasArgument("Ofacet"))
            {
/*            if($Ofacetetc && $Ofacetseg && $Ofacettok)
                $Ofacet = 'segtok';
            else*/
                $Ofacet = getArgument("Ofacet");
	          
            if($Oappprtty)
                $prtty = '1';
            else 
                $prtty= "0";


            if($mode === 'dry')
                {
                $command = "../bin/bracmat \"get'\\\"anasplit.bra\\\"\" \$F \$anasplitfile \"$Ofacet\" '0' '$prtty'";
                scrip($command);
                }
            else
                {
                $naam = $Ofacet."F";
                $command = "../bin/bracmat \"get'\\\"anasplit.bra\\\"\" $F $anasplitfile \"$Ofacet\" '0' '$prtty'";
                logit($command);

                if(($cmd = popen($command, "r")) == NULL)
                    {
                    throw new SystemExit();
                    }

                while($read = fgets($cmd))
                    {
                    }

                pclose($cmd);
                }
            }

// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $anasplitfile
//*/
        $tmpf = fopen($anasplitfile,'r');

        if($tmpf)
            {
            //logit('output from anasplit:');
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
    do_anasplit();
    }
catch (SystemExit $e)
    {
    header('HTTP/1.0 404 An error occurred: ' . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }
?>

