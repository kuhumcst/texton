# Administration
## GUI (Graphical User Interface) for administrative tasks
If the URL of front page of Text Tonsorium is https://xxx.yy/texton/, then adding an extra 'admin.html' brings you to the administrative page, where you can do many tasks:

1. [Register new tools and change the metadata of existing tools](#Adding-metadata-for-new-tools-and-maintaining-metadata-for-existing-ones).
2. [Reload the non-Java part of the Text Tonsorium program](#reload-the-non-java-part-of-the-text-tonsorium-program).
3. [Export all metadata to a dump file](#Export-all-metadata-to-a-dump-file).
4. [Import metadata from a dump file in another instance of Text Tonsorium](#Import-metadata-from-a-dump-file-in-another-instance-of-Text-Tonsorium).
5. [Check the current version of Bracmat](#Check-the-current-version-of-Bracmat).

## Administrative tasks for which there is no GUI
There are things that you sometimes need to do, but for which there is no web interface.

6. [Restart Text Tonsorium (including the Java code)](#Restart-Text-Tonsorium).
7. [Copy a dump file to the normally remote file location from where it can be imported using the web interface](#Copy-a-dump-file).
8. [Edit lists with feature values that the user (or the administrator) can select from, when using the web interface](#Expanding-and-editing-metadata-in-the-file-system).
9. [Integrate a new tool](#Integrate-a-new-tool).

## Adding metadata for new tools and maintaining metadata for existing ones
Part of the code in toolsprog.bra is dedicated to the registration of tools. The administrative interface for registration of tools is in the upper part of [http://localhost/texton/admin/](http://localhost/texton/admin.html). (This is in a local setting, e.g. a development machine.)
You can register new tools, change the metadata of existing ones and generate a PHP wrapper for a tool that extracts all the HTTP parameters that the tool needs.

There are many metadata related files, but the only ones that are affected by the web based GUI are /opt/texton/BASE/meta/tooladm and /opt/texton/BASE/meta/toolprop.
In the folder /opt/texton/BASE/meta/ and its subfolders are a number of files that contain the data with which drop down lists are filled in the GUI. If you think a value is missing in a dropdown list, then the corresponding metadata file has to be edited manually.

To be able to add or change metadata in the web GUI, you need to know the password. Per default, the password is a zero length ('blank') string. For instances of Text Tonsorium that are visible to other people than you alone, you must set a good password. How to do that is described in texton-Java/README.md. 

A second requirement for entering the registration web GUI is that you provide an email address. If you want to edit existing metadata, then the email address must be the same as that recorded in the metadata.

If your credentials are accepted, and you want to register a new tool, then you are led straight to an input form were you can start entering metadata. 
If you want to update existing metadate, you will first have to choose a tool from a drop down list before being led to the input form. In the latter case, the input form is filled with the existing metadata.

The registration form is divided in three parts.
1. The upper part is for boiler plate information: ToolID, Title, Description, Creator, Service URL of the tool, Inactive, etc. Of these fields, ToolID, Service URL of the tool and Inactive are the most 'technical' ones. ToolID is used as a unique key in the list of integrated tools. It is also used as part of a PHP variable. The Service URL of the tool is the address where the Text Tonsorium sends requests to each time it wants to activate the tool. Finally, if Inactive is checked, the tool is registered, but will not take part in any workflow. When registering a tool, Inactive is set per default. This flag can only be toggled when updating a tool.
2. Below the boiler plate information is the I/O (input and output) metadata that is used for knitting together tools in viable workflow designs. These metadata can occur in multiple 'incarnations'. Incarnations are invented for keeping apart metadata sets that cannot be combined. For example, one incarnation of a lemmatizer tool can handle Danish and needs tokenised, part of speech tagged text as input, while another incarnation can handle Czech tokenised text that must be without part of speech tags. The two incarnations cannot be combined into one, because it would imply that the lemmatizer also would work for Danish if the input is not POS-tagged. You as administrator do not have to worry about the creation of incarnations. This is done automatically. Also, after editing and saving metadata, the system may decide that the collection of metadata in all incarnations must be divided in a different way into incarnations.
3. At the bottom of the registration form are five buttons:
1. [Save metadata](#save-metadata)
2. [Replace metadata](#replace-metadata)
3. [Delete metadata](#delete-metadata)
4. [Show more entry fields](#show-more-entry-fields)
5. [PHP wrapper](#php-wrapper)

#### Save metadata
If you have been editing existing metadata, the following happens:
* If you made a change in the boiler plate section, the old values are replaced by the new ones.
* If you made changes in the I/O metadata, then these metadata are saved without overwriting the old metadata.

#### Replace metadata
This is like 'Save metadata' as far as boiler plate metadata is concerned. If you made changes to I/O metadata, then the old values are overwritten by the new ones.

#### Delete metadata
This button can have two different effects.
* The current incarnation is deleted. The number of incarnations decreases by one.
* If there are no I/O metadata at all (i.e. zero incarnations), pressing this button removes the boiler plate metadata. Hereafter the tool is no longer known to Text Tonsorium.

#### Show more entry fields
Almost any field in the I/O section of the registration form can occur more than once. Such fields are marked with check boxes named 'more' or 'Add an input/output combination'. When you check such boxes, the GUI does not immediately add the requested extra fields. For that, you press this button.

#### PHP wrapper
When you are content with all the registered metadata, you press this button to generate a PHP wrapper that you can use to integrate the actual tool in Text Tonsorium. Often, the tool is a command line tool. In other cases the tool is already accessible over the internet or intranet. And finally, sometimes the tool can be implemented in PHP itself. In each of these cases it is advisable to use the produced PHP wrapper.

To leave the registration form, just enter another URL in the browser's address bar.

## Reload the non-Java part of the Text Tonsorium program
Most of the program code is written in the Bracmat, a programming language with some programming constructs that make it equisitely apt to the task of "dynamic programming with memoization". The Bracmat code is interpreted rather than compiled, and can be replaced by an updated version while the Java-code of the Text Tonsorium is still running. Because many improvements of the Bracmat program code have to do with the computation of workflow candidates, we have chosen to reset memoized results when the Bracmat code is replaced. The computation of workflow candidates will therefore be slower for some time after reloading.

Reloading is advisable after making a `git pull' of this repositorium that includes a new version of the file `toolsProg.bra'. It is also advisable before and after reading a dump file containing metadata. (See below.)

## Export all metadata to a dump file
As a way to make a backup of the metadata, and also as a way to distribute metadata to similar instances of Text Tonsorium, metadata can be saved to a file with the press of a button. The dump file - the file with the saved metadata - is not downloadable via the web interface, but resides in the same folder as the Bracmat program code, e.g. /opt/texton/BASE on the server from where the Text Tonsorium web application is served. You must have root (sudo) access rights to be able to see the contents of the file.

When exporting, you can choose to also export metadata that is specific for the specific Text Tonsorium installation, such as information about jobs and about the whereabouts of the integrated tools and their 'inactivity' status. To export such metadata, place a checkmark in the checkbox.

## Import metadata from a dump file in another instance of Text Tonsorium
The inverse of exporting metadata is of course importing such data. You can choose to overwrite the current production data with the production data in the dump file. This is in general not what you want.

If you want to import a dump file that was exported from the same instance of Text Tonsorium, and if you never touched (edited, deleted, moved to another location) the dump file, you do not need to do anything outside the administrative GUI. If, on the other hand, you want to import a dump file that was created in another instance of the Text Tonsorium, then you need to copy the dump file from the original location to the new one. Dump files are always located in the same folder as the file 'toolsProg.bra'. Make sure that they are readable for the tomcat user. The name of a dump file always starts with with the string 'alltables'. 

## Check the current version of Bracmat
If you want to know whether the Bracmat JNI (Java Native Interface) is the latest version, press the button. Compare the date with the date of the files in the [Bracmat repo](https://github.com/BartJongejan/Bracmat).

## Restart Text Tonsorium 
To make a clean start of the Text Tonsorium (the Bracmat part as well as the Java part), do the following

* stop tomcat
* remove /opt/texton/BASE/job/recentTasks
* start tomcat

## Copy a dump file
Dump files are saved in the same folder as the toolsProg.bra" file, e.g. as /opt/texton/BASE/alltables.bra. Use a tool like scp to copy alltables.bra to another installation of Text Tonsorium.

## Expanding and editing metadata in the file system

The Text Tonsorium does not depend on a database management system like MySQL, yet it uses several 'tables'. (They are not really tables, but data structured in trees, like what you normally see expressed in XML and JSON.) Each table is in a separate file that can be edited in every plain text editor. So it is possible to change metadata if one has access to the files. Where are the files? Open the file 'properties_ubuntu.xml' (See [https://github.com/kuhumcst/texton-Java/blob/master/properties_ubuntu.xml]. There it is:

```xml
<entry key="toolsHome">/opt/texton/BASE/</entry>
```

So, per default the metadata are somewhere under '/opt/texton/BASE/' and its subfolders. The metadata under '/opt/texton/BASE/job' is very volatile and you should not edit those. The metadata under '/opt/texton/BASE/meta', however, are very static, and you have to edit them to influence how the Text Tonsorium sees the world of tools. The folder '/opt/texton/BASE/data' does not contain metadata, but input, output and intermediary data.

These are things you need to know about editing the tables:

1. [Finding the file you need to edit](#Finding-the-file-you-need-to-edit)
2. [What is in the files](#What-is-in-the-files)
3. [Editing selection lists](#Editing-selection-lists)
4. [Adding subspecifications](#Adding-subspecifications)

#### Finding the file you need to edit

In '/opt/texton/BASE/' you find the file called 'where' that tells where each table is stored, including 'where' itself. Here are the contents of 'where' as they currently (August 2022) look like. What you see are a number of parenthesised expressions. Each of these has a dot '.' near the end. The part between the dot and the closing parenthesis is a path that starts at '/opt/texton/BASE/' (or whatever place the file toolsProg.bra is located). So the path '/' means '/opt/texton/BASE/' and the path 'meta/feature/' means '/opt/texton/BASE/meta/feature'. The part between the opening parenthesis and the dot is the list of filenames of the tables in the aforementioned file location. So `(AAA changelog footer where./)` means that there are files '/opt/texton/BASE/AAA', '/opt/texton/BASE/changelog', '/opt/texton/BASE/footer' and '/opt/texton/BASE/where'. Whether things are listed on a single line or over several lines makes no difference.


```
  (AAA changelog footer where./)
  (   CTBs
      jobAbout
      jobNr
      jobs
      Uploads
      zippedresults
      ItemGroupsCache
      recentTasks
      wrkflws
  . job/
  )
  (   features
      HTTP-status-codes
      ISO-639
      tooladm
      toolprop
      UIlanguage
      SuperSets
      TEImetadata
      Typeface
  . meta/
  )
  (   facets
      fileFormats
      linguae
      periods
      presentations
      vulticuli
      ambiguity
      smell
  . meta/feature/
  )
  (   annotationStyles
      basistextStyles
      conllStyles
      flatFileTypes
      htmlStyles
      imageStyles
      jsonStyles
      morfSets
      sndStyles
      tagSets
      tokenisationStyles
      vidStyles
  . meta/style/
  )
```

#### What is in the files

##### self descriptive files
<dl>
  <dt>AAA
  </dt><dd>This file contains the file name of the dump file where this file (AAA) was extracted from.</dd>
  <dt>changelog
  </dt><dd>Here we tell end users about new features in tools and in Text Tonsorium itself.</dd>
     <dt>footer
  </dt><dd>If you want to customize the front page with e.g. logos and links in a footer, this is the place. This file is per default empty. The file can define some paragraphs (p), linebreaks (br), etc. Note that the format is not XML, but Bracmat. 
<pre>
  ( p
  .   (class.indent)
    , ( small
      .
        , ( a
          .   (href."/was")
            , (span.(class.s),"Accessibility (Danish)")
          )
      )
  )
  </pre>
is equivalent to
<pre>
  &lt;p class="indent"&gt;
      &lt;small&gt;
          &lt;a href="/was"&gt;
              &lt;span class="s"&gt;
                  Accessibility (Danish)
              &lt;/span&gt;
          &lt;/a&gt;
      &lt;/small&gt;
  &lt;/p&gt;  </pre>
  
  </dd>
  <dt>where
  </dt><dd>This file describes where each of the files that can be exported to and imported from a dump file is located in the file system.
  </dd>
  </dl>
 
##### job related files

<dl>
  <dt>CTBs</dt>
  <dd>
    This file contains information that is needed to fill out the content of `TEI/teiHeader/fileDesc/publicationStmt/idno` elements in Clarin DK annotation files. 
  </dd>
  <dt>jobAbout</dt>
  <dd>
    This file contains fields for a job number, an email address (currently not used), a human readable description of the current workflow, and user provided metadata. The last field contains (meta)data if the user has asked for output in the Clarin-DK annotation format.
  </dd>
  <dt>jobNr</dt>
  <dd>
    This file contains nothing but a number, in plain text. Each time a user presses the 'submit' button to enact a workflow, this number is read from the jobNr file, used to identify the job, incremented, and finally saved to the jobNr file.
  </dd>
  <dt>jobs</dt>
  <dd>
    Contains the current status of jobs. Each job (identified by a job number, see above) consists one or more steps. `jobs` has information about the dependencies between the steps (whether they are waiting, running, done or aborted), about the tool that has to play out the step, its input(s) and output, and the parameters that have to be sent to the tool together with the input(s).
  </dd>
  <dt>Uploads</dt>
  <dd>
    Contains information about each uploaded file.
  </dd>
  <dt>zippedresults</dt>
  <dd>
    Contains the name of all available archive files with job results.
  </dd>
  <dt>ItemGroupsCache</dt>
  <dd>
    If the user uploads files of different character (e.g. some PDF files and some HTML files), then Text Tonsorium groups them, so each group is homogeneous enough to be handled by the same workflow. It is up to the user to point out the group he or she wants to continue with. Before the user has made this choice, Text Tonsorium has to keep all possibilities in the air. This is the purpose of the ItemGroupsCache file. In addition, ItemGroupsCache, for each group of files, keeps record the bookmarked workflows that are compatible with those files. 
  </dd>
  <dt>recentTasks</dt>
  <dd>
    Computation of workflow candidates can take relatively long time. To save time and CPU cycles, Text Tonsorium remembers the outcome of the last 30 or so workflow computation tasks, together with the input that went into the computation. If the Text Tonsorium for some reason is stopped and started again, it reads this file. It should be noted, though, that this file is best deleted before starting Text Tonsorium if the reason for restarting Text Tonsorium was that some improvement in the workflow computation had been made.
  </dd>
  <dt>wrkflws</dt>
  <dd>
    Contains the bookmarked workflows, together with their input specs. When the user has uploaded input to the Text Tonsorium, it consults this file to see which bookmarked workflows can be proposed to the user.
  </dd>
</dl>

##### sundry metadata

<dl>
  <dt>features</dt>
  <dd>
    Input, output and the I/O specs of tools are described in terms of a set of values. Each value belongs to a feature, e.g. 'type of content', 'language', 'file format', etc. The file called 'features' contains, for each feature, a number of metadata fields. Some of these fields are optional. Here are the fields for the 'Type of Content' feature.
    <pre>
  ( (inDex.A)
    (name."Type of content" Annotationstype)
    (short.facet)
    ( description
    .   "Subtype of resource, e.g. basis text, tokenisation, alphabetic list."
        "Subtype af resource, fx basistekst, tokens, alfabetisk liste."
    )
    (table.facets)
    ( specificationTable
    .   (pos.tagSets)
        (mrf.morfSets)
        (tok.tokenisationStyles)
    )
    ( sourcehelp
    .   "The default type of content of the input is \"text\". Change it if necessary."
        "Normalt antager Text Tonsorium at inputtet ikke er annoteret. Ændr til en anden værdi om nødvendigt."
    )
    ( goalhelp
    .   "You fill this out in most use cases. Sometimes (e.g. if you are merely interested in a file format transformation), you choose the same value as in the input."
        "Udfyld i de fleste sammenhænge. Du kan nogle gange vælge samme værdi som i inputtet, fx når formålet er at transformere filformatet, men ikke indholdstypen."
    )
  )      
    </pre>
    <dl>
      <dt>inDex</dt>
      <dd>Indicates the order in which features are shown to the user. A comes before B, B before C, etc. </dd>
      <dt>name</dt>
      <dd>The name of the feature, in one or two languages. As we also will see below, most of Text Tonsorium is bilingual. The first language is English and the second language is Danish. So in the example, "Type of content" is the English name of the feature and Annotationstype the Danish one. (The reason why "Type of content" is enclosed in quotation marks is that the enclosed string contains blanks.)</dd>
      <dt>short</dt>
      <dl>The internal name for the feature.</dl>
      <dt>description</dt>
      <dl>Some text, in two languages, that describe the featire.</dl>
      <dt>table</dt>
      <dl>The name of the table that lists the feature values from which the user can choose.</dl>
      <dt>specificationTable</dt>
      <dl>If there are values that can be subspecified, these values are listed together with their possible subspecifications in the table that has a name that is the value of the specificationTable field.</dl>
      <dt>sourcehelp</dt>
      <dl>Provides help text when the user specifies the input.</dl>
      <dt>goalhelp</dt>
      <dl>Provides help text when the user specifies the output.</dl>
    </dl>    
  </dd>
  <dt>HTTP-status-codes</dt>
  <dd>
    A list of HTTP status codes together with meaning and some explanation. E.g.
    <pre>
  ( 201
  . Created
  . "The request has been fulfilled and resulted in a new resource being created."
  )    
    </pre>
  </dd>
  <dt>ISO-639</dt>
  <dd>Two and three letter codes for languages</dd>
  <dt>tooladm</dt>
  <dd>
    Table that contains boiler plate metadata for all integrated tools. These data can be edited in the GUI. An example:
    <pre>
  ( (ToolID.Brill-tagger)
    (PassWord.)
    (ContactEmail."x@x.xxx")
    (Version.1cst)
    (Title."Brill tagger")
    (ServiceURL."http://localhost/BrillTagger/")
    (Publisher.CST)
    (ContentProvider."cst.ku.dk")
    (Creator.Brill)
    (InfoAbout."https://nlpweb01.nors.ku.dk/download/tagger/")
    ( Description
    . "Part-of-speech tagger: Marks each word in a text with information about word class and morphological features."
    )
    (ExternalURI.)
    (XMLparms.)
    (PostData.)
    (Inactive.)
  )
    </pre>
    <dl>
      <dt>ToolID</dt>
      <dd>Unique name of tool, for internal use only</dd>
      <dt>PassWord</dt>
      <dd>Needed if one wants to pass on admistration rights to someone with a different email address. This procedure is circumvented by editing the table outside the GUI.</dd>
      <dt>ContactEmail</dt>
      <dd>If you enter the wrong email before updating a tool, you won't have access, unless you know the password.</dd>
      <dt>Version</dt>
      <dd>Version of the tool</dd>
      <dt>Title</dt>
      <dd>Name of the tool. Monolingual!</dd>
      <dt>ServiceURL</dt>
      <dd>The URL where the Text Tonsorium sends its requests to when it wants to activate the tool.</dd>
      <dt>Publisher</dt>
      <dd>(No explanation available. Sorry for that.)</dd>
      <dt>ContentProvider</dt>
      <dd>(No explanation available. Sorry for that.)</dd>
      <dt>Creator</dt>
      <dd>(No explanation available. Sorry for that.)</dd>
      <dt>InfoAbout</dt>
      <dd>Link to a page that offers information about the tool.</dd>
      <dt>Description</dt>
      <dd>A description of the tool in rather general terms. The languages and file formats supported by the tool, for example, need not be mentioned here.</dd>
      <dt>ExternalURI</dt>
      <dd>Link to another page where the tool can be tried out.</dd>
      <dt>XMLparms</dt>
      <dd>If 'on', Text Tonsorium should send the parameters in some XML. Not implemented!</dd>
      <dt>PostData</dt>
      <dd>If 'on', input to a tool is sent alongside the parameters in a POST request. The default is GET, which means that input isn't pushed to the tool by pulled by the tool.</dd>
      <dt>Inactive</dt>
      <dd>'on' when a new tool is registered and also when later set to 'on'. The value 'on' makes the tool invisible to the workflow computation algorithm.</dd>
    </dl>
  </dd>
  <dt>toolprop</dt>
  <dd>
    Contains for each incarnation for each tool the input/output specs for each feature that is relevant for the tool's proper working. This file can be edited using the GUI.
  </dd>
  <dt>UIlanguage</dt>
  <dd>
    A list with two elements: the ISO-639 codes for the default GUI language and for the second GUI language. Currently 'en' and 'da' respectively. The 'setLanguage' function is able to swap the order of the languages.
  </dd>
  <dt>SuperSets</dt>
  <dd>
    The user does not need to specify all features, and if a feature van be composed of several feature values, the user does not need to specify them all. To make the latter possible, the 'SuperSets' table lists how the value chosen by the user can be expanded to a multivalued, composite value. At the time of writing, the only feature for which this is relevant is the "Type of content" value ('facet' in internal speak).  
  </dd>
  <dt>TEImetadata</dt>
  <dd>
    This file defines the XML fields and their positions in the XML tree that together constitute much of the 'metadata' of a Clarin-dk output file.
  </dd>
  <dt>Typeface</dt>
  <dd>
    Defines the default typeface in the Text Tonsorium.
  </dd>
</dl>

##### Selection lists

There are two subfolders under '/opt/texton/BASE/meta', called 'feature' and 'style'. All files in these subdirectories have the same record structure:

    ("English name" "dansk navn".internalName."English comment" "dansk kommentar")

Some of the fields are optional: "dansk navn", "English comment" and "dansk kommentar". The purpose of these tables is to map internal names for feature values and for subspecifications of feature values to names that can be presented in 'human language' to English or Danish speakers. Whereas the human language names can contain any character, the internal names must be restricted to alphanumerical ASCII characters, since the are used in places that for historic reasons are best adapted to such characters, for example HTTP parameter names and their values.

#### Editing selection lists

One can edit the selection lists and for example change the English or Danish equivalent of some internal name. One can also add new records without further ado. The only thing one must be very careful with is deleting a record or changing an internal name. Before one does that, one should check in the 'toolsprop' and 'features' tables whether the involved internal name is used. Renaming internal names that are in use is a very bad idea, because one would not only have to edit the two or three involved tables, but also the PHP wrappers for the involved tools.

#### Adding subspecifications

As already hinted in the previous section, the 'features' table can mention feature values. Those are the feature values that can be subspecified. If you want to subspecify value 'meta' of feature 'socmed', then you have to first open the file 'features' and find the term that has a field `(short.socmed)`. Suppose you find that term, then you must find the field 'specificationTable'. If it isn't there, create the field `(specificationTable.(meta.facebMedia))` and save. Then create the file '/opt/texton/BASE/meta/style/facebMedia' and write e.g. 

```
  (WhatsApp.wa.)
  (Facebook.fb.)
  (Messenger.mes.)
```
From now on, the feature value 'faceb' of the feature 'socmed' can be subspecified as in `meta^wa`, `meta^fb` and `meta^mes`.

## Integrate a new tool
Integration of an NLP (or other) tool
Every tool that can run

1. in batch mode (i.e. without requiring user interaction while running)
2. under an operating system featuring a webserver that includes PHP
 
can be integrated in the Text Tonsorium.

This is how integration is done:

1. [Add the tool's metadata to the Text Tonsorium](#Adding-metadata-for-new-tools-and-maintaining-metadata-for-existing-ones).   
2. [Generate the PHP wrapper for that specific tool](#php-wrapper). Copy and paste the code to a file called 'index.php'.
3. [Edit index.php](#Editing-the-PHP-file).
4. [Copy index.php](#Copy-the-PHP-file) to a location where the webserver can see it.
5. [Tell the webserver under which condition to activate this index.php](#configuration), i.e. bind the tool's URL (as stated in the metadata) to the location where index.php is saved.

#### Editing the PHP file

Open index.php in an editor and search for the comments that say TODO. Add or edit code as you see necessary to run the tool.

The contents of index.php may seem overwhelming, but making the integration work is really simple. Most importantly, you have to look for this code:

```php
//* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $anasplitfile = tempFileName("anasplit-results");
        $corrmmand = "echo $echos >> $anasplitfile";
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
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $myveryfirsttoolfile
//*/
```

Where it says `//        TODO your code!`, you can start writing the PHP code that activates your tool. As the following comment shows, the output must be written to a very specific file, in this case called `$myveryfirsttoolfile`. And then you are almost done. The first line of the cited code above starts with two slashes (solidus = slash). Remove one of them! Your code will be commented out if you don't do this.

Your code must use the input data that was sent in the HTTP request by the Text Tonsorium. Input files are always parameters with names that end with a capital 'F'. Scroll through the PHP code to find them. If the tool receives only a single file, then this parameter is always called simply 'F' and the wrapper has already saved that file and bound its name to the PHP variable `$F`. So a hypothetical 'do nothing' tool could just do
");

```php
        system("cp $F $myveryfirsttoolfile");
```

Here we have used `os.system`. Normally, however, we use `popen` instead of `system`:

```php
        $myveryfirsttoolfile = tempFileName("firstTool");
        $command = 'python3 mylitllepython.py ' . $F . ' ' . $myveryfirsttoolfile;

        if(($cmd = popen($command, "r")) == NULL)
            {
            throw new SystemExit();
            }

        while($read = fgets($cmd))
            {
            }
        pclose($cmd);
```

Often, a tool needs two or more inputs. If that is the case, search for PHP variables that have names that start with a capital 'I' (for 'Input') and that end with 'F'. If your tool needs two different types of contents: tokens and PoS-tags, then these variables will be called `$IfacettokF` and `$IfacetposF`.

It is quite possible that your tool sometimes needs one input, and at other times needs more. This can be the case if the tool has more than one incarnation. So, for example, the CST lemmatizer sometimes runs with a single input file that contains both tokens, POS tags and perhaps even more types of contents. At other times it needs separate input files for tokens and for PoS tags. Therefore, the generated PHP-code says

```php
        $F = "";	/* Input (ONLY used if there is exactly ONE input to this workflow step) */
        $IfacetposF = "";	/* Input with type of content PoS-tags (PoS-tags) */
        $IfacettokF = "";	/* Input with type of content tokens (tokens) */
```

The comments following the PHP variables try to help you. If the wrapper receives a single file, the variable `$F` will contain the name of a file and both `$IfacetposF` and `$IfacettokF` will be empty strings and vice versa. In your code you should therefore check the emptyness of these variables to decide which branch it should take.

Per default, the PHP wrapper works synchronously, which means that it returns the result of the tool as the response to the HTTP request, accompanied by the return code 200. It is however possible to make it work asynchronously, which means that it returns 201 even before the tool is finished doing its thing. Then, when the tool is ready, the PHP code must POST the result to the Text Tonsorium. One should be careful with asynchronous tools; the Text Tonsorium will take advantage of the doubling of the interaction by sending two new requests, if there are enough jobs waiting to be run. Especially if the Text Tonsorium is fed with many uploaded texts (e.g. 100 text documents that all have to be syntactically annotated), a single asynchrounous tool will cause a broad fan of simultaneously running jobs. If the hardware can handle those, it's fine, and the results for all annotation tasks will be available rather quickly. But if there are not that many cores, the jobs will be plodding. The Text Tonsorium will try to restrict the number of running tasks to about 8, but there is no guarantee that will succeed.

If the tool you want to integrate already is a web app, then the easiest way to integrate it is to still generate and use the PHP app. Instead of running the code with `system` or `popen`, the wrapper can forward the request to the web app. There are several examples of such tools in this repo. (Search for functions called `http'.)

In case you need to debug the wrapper and the wrapper's communication with the tool, there is the function `logit`. There is also another function related to logging, called `loginit`. Both functions need to be edited a little bit before they become useful: the `return` statements at the start of the function bodies need to be commented out:

```php
function loginit()  /* Wipes the contents of the log file! TODO Change this behaviour if needed. */
    {
    //return;
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
    //return;
    global $toollog,$ftemp;
    $ftemp = fopen($toollog,'a');
    if($ftemp)
        {
        fwrite($ftemp,$str . "\n");
        fclose($ftemp);
        }
    }    
```

#### Copy the PHP file
All index.php files are in subfolders of the folder /opt/texton, as siblings of the folder `BASE' that, among many other things, contains the Bracmat code toolsProg.bra for the Text Tonsorium. You can name the subfolder as you like, but it is of course best to give it a name that reflects the tool's name. In this subfolder you can put other scripts (Python, Perl, etc.) that you want to activate from the index.php file.

#### Configuration
You need to instruct the webserver where the new tool resides that requests directed at the [Service URL of the tool](#Adding-metadata-for-new-tools-and-maintaining-metadata-for-existing-ones) must be handled by the index.php file that wraps around your tool. To that end, in Apache, you can take inspiration from the file apache2-sites/texton.conf, which has entries like

```
    Alias /CoreNLP /opt/texton/CoreNLP
    <Directory /opt/texton/CoreNLP>
        Options None
        AllowOverride None
        DirectoryIndex index.php
        Require all granted
    </Directory>
```
In this example, /CoreNLP is the path after the domain name in the Service URL of the tool. /opt/texton/CoreNLP is the folder where the index.php file is installed.
