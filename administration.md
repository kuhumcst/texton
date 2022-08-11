# Administration
## Web page for administrative tasks
If the URL of front page of Text Tonsorium is https://xxx.yy/texton/, then adding an extra 'admin.html' brings you to the administrative page, where you can do many tasks:

1. Register new tools.
2. Change the metadata of existing ones.
3. Reload the non-Java part of the Text Tonsorium program.
4. Export all metadata to a dump file.
5. Import all metadata from a dump file in another instance of Text Tonsorium, optionally without overwriting metadata that are specific to this particular instance.
6. Check the current version of Bracmat.

There are things that you sometimes need to do, but for which there is no web interface.
7. Restart Text Tonsorium (including the Java code).
8. Copy a dump file to the normally remote file location from where it can be imported using the web interface.
9. Edit lists with feature values that the user (or the administrator) can select from, when using the web interface.

## (1 and 2) Adding metadata for new tools and maintaining metadata for existing ones
Part of the code in toolsprog.bra is dedicated to the registration of tools. The administrative interface for registration of tools is in the upper part of [http://localhost/texton/admin/](http://localhost/texton/admin.html). (This is in a local setting, e.g. a development machine.)
You can register new tools, change the metadata of existing ones and generate a PHP wrapper for a tool that extracts all the HTTP parameters that the tool needs.

There are many metadata related files, but the only ones that are affected by the web based GUI are /opt/texton/BASE/meta/tooladm and /opt/texton/BASE/meta/toolprop.
In the folder /opt/texton/BASE/meta/ and its subfolders are a number of files that contain the data with which drop down lists are filled in the GUI. If you think a value is missing in a dropdown list, then the corresponding metadata file has to be edited manually.

To be able to add or change metadata in the web GUI, you need to know the password. Per default, the password is a zero length ('blank') string. For instances of Text Tonsorium that are visible to other people than you alone, you must set a good password. How to do that is described in texton-Java/README.md. 

A second requirement for entering the registration web GUI is that you provide an email address. If you want to edit existing metadata, then the email address must be the same as that recorded in the metadata.

If your credentials are accepted, and you want to register a new tool, then you are led straight to an input form were you can start entering metadata. 
If you want to update existing metadate, you will first have to choose a tool from a drop down list before being led to the input form. In the latter case, the input form is filled with the existing metadata.

The registration form is devided in two parts.
1. The upper part is for boiler plate information: ToolID, Title, Description, Creator, Service URL of the tool, etc. Of these fields, ToolID and Service URL of the tool are the most 'technical' one. ToolID is used as a unique key in the list of integrated tools. It is also used as part of a PHP variable. The Service URL of the tool is the address where the Text Tonsorium sends requests to each time it wants to activate the tool.
2. Below the boiler plate information is the I/O (input and output) metadata that is used for knitting together tools in viable workflow designs. These metadata can occur in multiple 'incarnations'. Incarnations are invented for keeping apart metadata sets that cannot be combined. For example, one incarnation of a lemmatizer tool can handle Danish and needs tokenised, part of speech tagged text as input, while another incarnation can handle Czech tokenised text that must be without part of speech tags. The two incarnations cannot be combined into one, because it would imply that the lemmatizer also would work for Danish if the input is not POS-tagged. You as administrator do not have to worry about the creation of incarnations. This is done automatically. Also, after editing and saving metadata, the system may decide that the collection of metadata in all incarnations must be divided in a different way into incarnations.

At the bottom of the registration form are five buttons:
1. Save metadata
2. Replace metadata
3. Delete metadata
4. Show more entry fields
5. PHP wrapper

### Details

1. Save metadata. If you have been editing existing metadata, the following happens:
* If you made a change in the boiler plate section, the old values are replaced by the new ones.
* If you made changes in the I/O metadata, then these metadata are saved without overwriting the old metadata.

2. Replace metadata. This is like 'Save metadata' as far as boiler plate metadata is concerned. If you made changes to I/O metadata, then the old values are overwritten by the new ones.

3. Delete metadata. This button can have two different effects.
* The current incarnation is deleted. The number of incarnations decreases by one.
* If there are no I/O metadata at all (i.e. zero incarnations), pressing this button removes the boiler plate metadata. Hereafter the tool is no longer known to Text Tonsorium.

4. Show more entry fields. Almost any field in the I/O section of the registration form can occur more than once. Such fields are marked with check boxes named 'more' or 'Add an input/output combination'. When you check such boxes, the GUI does not immediately add the requested extra fields. For that, you press this button.

5. PHP wrapper. When you are content with all the registered metadata, you press this button to generate a PHP wrapper that you can use to integrate the actual tool in Text Tonsorium. Often, the tool is a command line tool. In other cases the tool is already accessible over the internet or intranet. And finally, sometimes the tool can be implemented in PHP itself. In each of these cases it is advisable to use the produced PHP wrapper.

To leave the registration form, just enter another URL in the browser's address bar.

## Integration of NLP (or other) tool
Every tool that can run
1. in batch mode (i.e. without requiring interaction while running)
2. under an operating system featuring a webserver that includes PHP
can be integrated in the Text Tonsorium.

This is how integration is done
1. Add the tool's metadata to the Text Tonsorium.   
3. Generate the PHP wrapper for that specific tool. Copy and paste the code to a file called 'index.php'.
4. Open index.php and search for the comments that say TODO. Add or edit code as you see necessary to run the tool.
5. Copy index.php to a location where the webserver can see it.
6. Tell the webserver under which condition to activate this index.php, i.e. bind the tool's URL (as stated in the metadata) to the location where index.php is saved.

### Details

1. See the previous section.

2. See the previous section.

3. The contents of index.php may seem overwhelming, but making the integration work is really simple. Look for this code:

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

Where it says `//        TODO your code!`, you can start writing the PHP code that activates your tool. As the following comment shows, the output must be written to a very specific file, in this case called `$myveryfirsttoolfile`. And then you are almost done. The first line of the cited code above starts with two slashes (solidus = slash). Remove one of them! If you don't do this, your code will be commented out.

Your code must use the input data that was sent in the HTTP request by the Text Tonsorium. Input files are always parameters with names that end with a capital 'F'. Scroll through the PHP code to find them. If the tool receives only a single file, then this parameter is always called simply 'F' and the wrapper has already saved that file and bound its name to the PHP variable `$F`. So a hypothetical 'do nothing' tool could just do
");

```php
        system("cp $F $myveryfirsttoolfile");
```

Often, a tool needs two or more inputs. In that case, search for PHP variables that have names that start with a capital 'I' (for 'Input') and that end with 'F'. If your tool needs two different types of contents: tokens and PoS-tags, then these variables will be called `$IfacettokF` and `$IfacetposF`.

It is quite possible that your tool sometimes needs one input, and at other times needs more. This can be the case if the tool has more than one incarnation. So, for example, the CST lemmatizer sometimes runs with a single input file that contains both tokens, POS tags and perhaps even more types of contents. At other times it needs separate input files for tokens and for PoS tags. Therefore, the generated PHP-code says

```php
        $F = "";	/* Input (ONLY used if there is exactly ONE input to this workflow step) */
        $IfacetposF = "";	/* Input with type of content PoS-tags (PoS-tags) */
        $IfacettokF = "";	/* Input with type of content tokens (tokens) */
```

As you can see, the comments following the PHP variables try to help you. If the wrapper receives a single file, the variable `$F` will contain the name of a file and both `$IfacetposF` and `$IfacettokF` will be empty strings and vice versa. In your code you should therefore check the emptyness of these variables to decide which branch it should take.

Per default, the PHP wrapper works synchronously, which means that it returns the result of the tool as the response to the HTTP request, accompanied by the return code 200. It is however possible to make it work asynchronously, which means that it returns 201 even before the tool is finished doing its thing. Then, when the tool is ready, the PHP code must POST the result to the Text Tonsorium. One should be careful with asynchronous tools; the Text Tonsorium will take advantage of the doubling of the interaction by sending two new requests, if there are enough jobs waiting to be run. Especially if the Text Tonsorium is fed with many uploaded texts (e.g. 100 text documents that all have to be syntactically annotated), a single asynchrounous tool will cause a broad fan of simultaneously running jobs. If the hardware can handle those, it's fine, and the results for all annotation tasks will be available rather quickly. But if there are not that many cores, the jobs will be plodding. The Text Tonsorium will try to restrict the number of running tasks to about 8, but there is no guarantee that will succeed.

## Expanding and editing metadata in the file system

The Text Tonsorium does not depend on a database management system like MySQL, yet it uses several tables. Each table is in a separate file that can be edited in every plain text editor. So it is possible to change metadata if one has access to the files. Where are the files? Open the file 'properties_ubuntu.xml' (See [https://github.com/kuhumcst/texton-Java/blob/master/properties_ubuntu.xml]. There it is:

```xml
<entry key="toolsHome">/opt/texton/BASE/</entry>
```

So, per default the metadata are somewhere under '/opt/texton/BASE/' and its subfolders. The metadata under '/opt/texton/BASE/job' is very volatile and you should not edit those. The metadata under '/opt/texton/BASE/meta', however, is very static, and you have to edit them to influence how the Text Tonsorium sees the world of tools.
