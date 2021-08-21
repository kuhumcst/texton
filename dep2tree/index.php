<?php
header("Content-type:text/plain; charset=UTF-8");
putenv("LANG=da_DK.utf8");
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
ToolID         : dep2tree
PassWord       : 
Version        : 1.0
Title          : dependency2tree
Path in URL    : /dep2tree	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : GitHub
ContentProvider: https://github.com/boberle/dependency2tree
Creator        : Avatar Bruno Oberle
InfoAbout      : https://github.com/boberle/dependency2tree
Description    : Convert CoNLL output of a dependency parser into a latex or graphviz tree.
ExternalURI    : 
XMLparms       : 
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/dep2tree.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */
                
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
    
    //https://stackoverflow.com/questions/1707801/making-a-temporary-dir-for-unpacking-a-zipfile-into
    function destroydir(string $dir): bool 
        { 
        if (!is_dir($dir)) { return false; }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file)
            {
            if (is_dir("$dir/$file")) { destroydir("$dir/$file"); }
            else { unlink("$dir/$file"); }
            }
        return rmdir($dir); 
        }

    function tempdir(int $mode = 0700, bool $auto_delete = true): string 
        {
        $tmp = "";
        do 
            {
            if($tmp != "")
                unlink($tmp);
            $tmp = sys_get_temp_dir() . '/' . mt_rand();
            }
        while (!@mkdir($tmp, $mode));
	if ($auto_delete)
            {
            register_shutdown_function(function() use ($tmp) { destroydir($tmp); });
            }
        return $tmp;
        }
   
    function requestFile($requestParm) // e.g. "IfacettokF"
        {
        //logit("requestFile({$requestParm})");

        if(isset($_REQUEST[$requestParm]))
            {
            $urlbase = isset($_REQUEST["base"]) ? $_REQUEST["base"] : "http://localhost/toolsdata/";

            $item = $_REQUEST[$requestParm];
            $url = $urlbase . urlencode($item);
            //logit("requestParm:$requestParm");
            //logit("urlbase:$urlbase");
            //logit("item:$item");
            //logit("url[$url]");

            $handle = fopen($url, "r");
            if($handle == false)
                {
                //logit("Cannot open url[$url]");
                return "";
                }
            else
                {
                $tempfilename = tempFileName("dep2tree_{$requestParm}_");
                $temp_fh = fopen($tempfilename, 'w');
                if($temp_fh == false)
                    {
                    fclose($handle);
                    //logit("handle closed. Cannot open $tempfilename");
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
        //logit("empty");
        return "";
        }    

    function do_dep2tree()
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
        $Ifacet_lem_mrf_ner_pos_seg_stx_tok = false;	/* Type of content in input is lemmas (Lemma) and morphological features (morfologiske træk) and name entities (Navne) and PoS-tags (PoS-tags) and segments (Sætningssegmenter) and syntax (dependency structure) (Syntaks (dependensstruktur)) and tokens (Tokens) if true */
        $Ifacet_lem_mrf_pos_seg_stx_tok = false;	/* Type of content in input is lemmas (Lemma) and morphological features (morfologiske træk) and PoS-tags (PoS-tags) and segments (Sætningssegmenter) and syntax (dependency structure) (Syntaks (dependensstruktur)) and tokens (Tokens) if true */
        $Ifacet_lem_pos_seg_stx_tok = false;	/* Type of content in input is lemmas (Lemma) and PoS-tags (PoS-tags) and segments (Sætningssegmenter) and syntax (dependency structure) (Syntaks (dependensstruktur)) and tokens (Tokens) if true */
        $Ifacet_pos_seg_stx_tok = false;	/* Type of content in input is PoS-tags (PoS-tags) and segments (Sætningssegmenter) and syntax (dependency structure) (Syntaks (dependensstruktur)) and tokens (Tokens) if true */
        $Iformatconll = false;	/* Format in input is CoNLL if true */
        $Ofacetpos = false;	/* Type of content in output is PoS-tags (PoS-tags) if true */
        $Ofacetseg = false;	/* Type of content in output is segments (Sætningssegmenter) if true */
        $Ofacetstx = false;	/* Type of content in output is syntax (dependency structure) (Syntaks (dependensstruktur)) if true */
        $Ofacettok = false;	/* Type of content in output is tokens (Tokens) if true */
        $Oformathtml = false;	/* Format in output is HTML if true */
        $IformatconllclU = false;	/* Style of format CoNLL in input is CoNLL-U (10 columns)CoNLL-U (10 kolonner) if true */
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

/************************
* input/output features *
************************/
        if( hasArgument("Ifacet") )
            {
            $Ifacet_lem_mrf_ner_pos_seg_stx_tok = existsArgumentWithValue("Ifacet", "_lem_mrf_ner_pos_seg_stx_tok");
            $Ifacet_lem_mrf_pos_seg_stx_tok = existsArgumentWithValue("Ifacet", "_lem_mrf_pos_seg_stx_tok");
            $Ifacet_lem_pos_seg_stx_tok = existsArgumentWithValue("Ifacet", "_lem_pos_seg_stx_tok");
            $Ifacet_pos_seg_stx_tok = existsArgumentWithValue("Ifacet", "_pos_seg_stx_tok");
            $echos = $echos . "Ifacet_lem_mrf_ner_pos_seg_stx_tok=$Ifacet_lem_mrf_ner_pos_seg_stx_tok " . "Ifacet_lem_mrf_pos_seg_stx_tok=$Ifacet_lem_mrf_pos_seg_stx_tok " . "Ifacet_lem_pos_seg_stx_tok=$Ifacet_lem_pos_seg_stx_tok " . "Ifacet_pos_seg_stx_tok=$Ifacet_pos_seg_stx_tok ";
            }
        if( hasArgument("Iformat") )
            {
            $Iformatconll = existsArgumentWithValue("Iformat", "conll");
            $echos = $echos . "Iformatconll=$Iformatconll ";
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacetpos = existsArgumentWithValue("Ofacet", "pos");
            $Ofacetseg = existsArgumentWithValue("Ofacet", "seg");
            $Ofacetstx = existsArgumentWithValue("Ofacet", "stx");
            $Ofacettok = existsArgumentWithValue("Ofacet", "tok");
            $echos = $echos . "Ofacetpos=$Ofacetpos " . "Ofacetseg=$Ofacetseg " . "Ofacetstx=$Ofacetstx " . "Ofacettok=$Ofacettok ";
            }
        if( hasArgument("Oformat") )
            {
            $Oformathtml = existsArgumentWithValue("Oformat", "html");
            $echos = $echos . "Oformathtml=$Oformathtml ";
            }

/*******************************
* input/output features styles *
*******************************/
        if( hasArgument("Iformatconll") )
            {
            $IformatconllclU = existsArgumentWithValue("Iformatconll", "clU");
            $echos = $echos . "IformatconllclU=$IformatconllclU ";
            }
        if( hasArgument("Oformathtml") )
            {
            $OformathtmlROTM = existsArgumentWithValue("Oformathtml", "ROTM");
            $echos = $echos . "OformathtmlROTM=$OformathtmlROTM ";
            }

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $dep2treefile = tempFileName("dep2tree-results");
        $command = "echo $echos >> $dep2treefile";
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
        $dep2treefile = tempFileName("dep2tree-results");
        $nocomment = tempFileName("dep2tree-nocomment");
        $lsodir = tempFileName("dep2tree-lsodir");
        $odir = tempdir();
        $out = array();
        $data = file($F);
        foreach($data as $line) 
            {
            if(!str_starts_with(trim($line),"#"))
                {
                $out[] = $line;
                }
            }
        $fp = fopen($nocomment, "w+");
        flock($fp, LOCK_EX);
        foreach($out as $line)
            {
            fwrite($fp, $line);
            }
        flock($fp, LOCK_UN);
        fclose($fp);  
        $command = "export LANG=en_US.UTF-8 && python3 dependency2tree.py -o $odir/D.svg -c $nocomment --ignore-double-indices";
        if(($cmd = popen($command, "r")) == NULL){throw new SystemExit();} // instead of exit()
        while($read = fgets($cmd)){}
        pclose($cmd);
        $odirlst = scandir($odir);
	
        $fp = fopen($lsodir, "w+");
        flock($fp, LOCK_EX);
        foreach($odirlst as $line)
            {
            fwrite($fp, $line . "\n");
            }
        flock($fp, LOCK_UN);
        fclose($fp);
        $command = "../bin/bracmat 'get\$\"svghtml.bra\"' '$odir' '$lsodir' '$F' '$nocomment' '$dep2treefile'";
        if(($cmd = popen($command, "r")) == NULL){throw new SystemExit();} // instead of exit()
        while($read = fgets($cmd)){}
        pclose($cmd);
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $dep2treefile
//*/
        $tmpf = fopen($dep2treefile,'r');

        if($tmpf)
            {
            //logit('output from dep2tree:');
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
    do_dep2tree();
    }
catch (SystemExit $e) 
    { 
    header('HTTP/1.0 404 An error occurred: ' . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }
?>

