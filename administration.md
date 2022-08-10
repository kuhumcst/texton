# Administration
## Adding metadata for new tools and maintaining metadata for existing ones
Part of the code in toolsprog.bra is dedicated registration of tools. The web GUI interface for registration of tools is in the upper part of [http://localhost/texton/admin/](http://localhost/texton/admin.html). (This is in a local setting).
You can 
1. register new tools
2. change the metadata of existing ones
3. generate a PHP wrapper for a tool that extracts all the HTTP parameters that the tool needs
4. export all metadata to a file
5. import all metadata from a file in another instance of Text Tonsorium, optionally without overwriting metadata that are specific to this particular instance.

There are many metadata related files, but the only ones that are affected by the web based GUI are /opt/texton/BASE/meta/tooladm and /opt/texton/BASE/meta/toolprop.
In the folder /opt/texton/BASE/meta/ and its subfolders are a number of files that contain the data with which drop down lists are filled in the GUI. If you think a value is missing in a dropdown list, then the corresponding metadata file has to be edited directly.

To be able to add or change metadata in the web GUI you need to know the password. Per default, the password is a zero length ('blank') string. For instances of Text Tonsorium that are visible to other people than you alone, you must set a good password. How to do that is described in 
texton-Java/README.md 

A second requirement for entering the registration web GUI is that you provide an email address. If you want to edit existing metadata, then the email address must be the same as that recorded in the metadata.

If your credentials are accepted, and you want to register a new tool, then you are led straight to an input form were you can start entering metadata. 
If you want to update existing metadate, you will first have to choose a tool from a drop down list before being led to the input form. In the latter case, the input form is filled with the existing metadata.

The registration form is devided in two parts.
1. The upper part is for boiler plate information: name, description, author, URL, etc.
2. Below the boiler plate information is the I/O (input and output) metadata that is used for knitting together tools in viable workflow designs. These metadata can occur in multiple 'incarnations'. Incarnations are invented for keeping apart metadata sets that cannot be combined. For example, one incarnation of a lemmatizer tool can handle Danish and needs tokenised, part of speech tagged text as input, while another incarnation can handle Czech tokenised text that must be without part of speech tags. The two incarnations cannot be combined into one, because it would imply that the lemmatizer also would work for Danish if the input is not POS-tagged. You as administrator do not have to worry about the creation of incarnations. This is done automatically. Also, after editing and saving metadata, the system may decide that the collection of metadata in all incarnations must be divided in a different way into incarnations.

At the bottom of the registration form are five buttons:
1. Save metadata
2. Replace metadata
3. Delete metadata
4. Show more entry fields
5. PHP wrapper

Details:

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
1. Add the tool's metadata to the Text Tonsorium
   See the previous section.
3. Generate the PHP wrapper for that specific tool. Copy and paste the code to a file called 'index.php'.
4. Open index.php and search for the comments that say TODO
5. Copy index.php to a location where the webserver can see it.
6. Tell the webserver under which condition to activate this index.php, i.e. bind the tool's URL (as stated in the metadata) to the location where index.php is saved.
Sometimes, a tool is already 
