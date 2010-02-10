<?php
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
* 
* $Id$
*/


//Ensure script is not run directly, avoid path disclosure
include_once("login_check.php");

if ($action == "addgroup")
{
    $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    $grplangs[] = $baselang;
    $grplangs = array_reverse($grplangs);

    $newgroupoutput = PrepareEditorScript();
    $newgroupoutput .= "<div class='header'>".$clang->gT("Add question group")."</div>\n";


//    $newgroupoutput .="<table width='100%' border='0'  class='tab-page'>\n\t<tr><td>\n"
    $newgroupoutput .="\n"
    .  '<div class="tab-pane" id="tab-pane-newgroup">';
    $newgroupoutput .= "<form action='$scriptname' id='newquestiongroup' name='newquestiongroup' method='post' onsubmit=\"if (1==0 ";

    foreach ($grplangs as $grouplang)
    {
      $newgroupoutput .= "|| document.getElementById('group_name_$grouplang').value.length==0 ";
    }
    $newgroupoutput .=" ) {alert ('".$clang->gT("Error: You have to enter a group title for each language.",'js')."'); return false;}\" >";

    foreach ($grplangs as $grouplang)
    {
        $newgroupoutput .= '<div class="tab-page"> <h2 class="tab">'.GetLanguageNameFromCode($grouplang,false);
        if ($grouplang==$baselang) {$newgroupoutput .= '('.$clang->gT("Base Language").')';}
        $newgroupoutput .= "</h2><ul>"
        . "<li>"
        . "<label for='group_name_$grouplang'>".$clang->gT("Title").":</label>\n"
        . "<input type='text' size='80' maxlength='100' name='group_name_$grouplang' id='group_name_$grouplang' /><font color='red' face='verdana' size='1'> ".$clang->gT("Required")."</font></li>\n"
        . "\t<li><label for='description_$grouplang'>".$clang->gT("Description:")."</label>\n"
        . "<textarea cols='80' rows='8' id='description_$grouplang' name='description_$grouplang'></textarea>"
	    . getEditor("group-desc","description_".$grouplang, "[".$clang->gT("Description:", "js")."](".$grouplang.")",$surveyid,'','',$action)
	    . "</li>\n"
        . "</ul>"
    	. "\t<p><input type='submit' value='".$clang->gT("Add Group")."' />\n"
	    . "</div>\n";
    }

    $newgroupoutput.= "<input type='hidden' name='action' value='insertnewgroup' />\n"
    . "<input type='hidden' name='sid' value='$surveyid' />\n"
    . "</form>\n";


    // Import TAB
    $newgroupoutput .= '<div class="tab-page"> <h2 class="tab">'.$clang->gT("Import question group")."</h2>\n";
    $newgroupoutput.= ""
    . "<form enctype='multipart/form-data' id='importgroup' name='importgroup' action='$scriptname' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
    . "<ul>\n"
    . "<li>\n"
    . "<label for='the_file'>".$clang->gT("Select CSV File:")."</label>\n"
    . "<input id='the_file' name=\"the_file\" type=\"file\" size=\"35\" /></li>\n"
    . "<li><label for='translinksfields'>".$clang->gT("Convert resources links?")."</label>\n"
    . "<input id='translinksfields' name=\"translinksfields\" type=\"checkbox\" checked=\"checked\"/></li></ul>\n"
    . "\t<p><input type='submit' value='".$clang->gT("Import Group")."' />\n"
    . "\t<input type='hidden' name='action' value='importgroup' />\n"
    . "\t<input type='hidden' name='sid' value='$surveyid' />\n"
    . "\t</form>\n";

    // End Import TABS
    $newgroupoutput.= "</div>";

	// End of TABS
    $newgroupoutput.= "</div>";

    $newgroupoutput.= ""
	."<script type='text/javascript'>\n"
	."<!--\n"
	."document.getElementById('group_name_$grouplang').focus();\n"
	."//-->\n"
	."</script>\n";
}


if ($action == "editgroup")
{
    $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);

    $grplangs[] = $baselang;
    $grplangs = array_flip($grplangs);

    $egquery = "SELECT * FROM ".db_table_name('groups')." WHERE sid=$surveyid AND gid=$gid";
    $egresult = db_execute_assoc($egquery);
    while ($esrow = $egresult->FetchRow())
    {
        if(!array_key_exists($esrow['language'], $grplangs)) // Language Exists, BUT ITS NOT ON THE SURVEY ANYMORE.
        {
            $egquery = "DELETE FROM ".db_table_name('groups')." WHERE sid='{$surveyid}' AND gid='{$gid}' AND language='".$esrow['language']."'";
            $egresultD = $connect->Execute($egquery);
        } else {
            $grplangs[$esrow['language']] = 99;
        }
        if ($esrow['language'] == $baselang) $basesettings = array('group_name' => $esrow['group_name'],'description' => $esrow['description'],'group_order' => $esrow['group_order']);

    }

    while (list($key,$value) = each($grplangs))
    {
        if ($value != 99)
        {
            $egquery = "INSERT INTO ".db_table_name('groups')." (gid, sid, group_name, description,group_order,language) VALUES ('{$gid}', '{$surveyid}', '{$basesettings['group_name']}', '{$basesettings['description']}','{$basesettings['group_order']}', '{$key}')";
            $egresult = $connect->Execute($egquery);
        }
    }
    
    $egquery = "SELECT * FROM ".db_table_name('groups')." WHERE sid=$surveyid AND gid=$gid AND language='$baselang'";
    $egresult = db_execute_assoc($egquery);
    $editgroup = PrepareEditorScript();
    $editgroup .= "<div class='header'>".$clang->gT("Edit Group")."</div>\n"
    . "<form name='frmeditgroup' id='frmeditgroup' action='$scriptname' method='post'>\n"
    . '<div class="tab-pane" id="tab-pane-group-'.$gid.'">';

    $esrow = $egresult->FetchRow();
    $editgroup .= '<div class="tab-page"> <h2 class="tab">'.getLanguageNameFromCode($esrow['language'],false);
    $editgroup .= '('.$clang->gT("Base Language").')';
    $esrow = array_map('htmlspecialchars', $esrow);
    $editgroup .= '</h2><ul>';
    $editgroup .= "\t<li><label for='group_name_{$esrow['language']}'>".$clang->gT("Title").":</label>\n"
    . "<input type='text' maxlength='100' size='80' name='group_name_{$esrow['language']}' id='group_name_{$esrow['language']}' value=\"{$esrow['group_name']}\" />\n"
    . "\t</li>\n"
    . "\t<li><label for='description_{$esrow['language']}'>".$clang->gT("Description:")."</label>\n"
    . "<textarea cols='70' rows='8' id='description_{$esrow['language']}' name='description_{$esrow['language']}'>{$esrow['description']}</textarea>\n"
    . getEditor("group-desc","description_".$esrow['language'], "[".$clang->gT("Description:", "js")."](".$esrow['language'].")",$surveyid,$gid,'',$action)
    . "\t</li></ul></div>"; 


    $egquery = "SELECT * FROM ".db_table_name('groups')." WHERE sid=$surveyid AND gid=$gid AND language!='$baselang'";
    $egresult = db_execute_assoc($egquery);
    while ($esrow = $egresult->FetchRow())
    {
        $editgroup .= '<div class="tab-page"> <h2 class="tab">'.getLanguageNameFromCode($esrow['language'],false);
        $esrow = array_map('htmlspecialchars', $esrow);
        $editgroup .= '</h2><ul>';
        $editgroup .= "\t<li><label for='group_name_{$esrow['language']}'>".$clang->gT("Title").":</label>\n"
        . "<input type='text' maxlength='100' size='80' name='group_name_{$esrow['language']}' id='group_name_{$esrow['language']}' value=\"{$esrow['group_name']}\" />\n"
        . "\t</li>\n"
        . "\t<li><label for='description_{$esrow['language']}'>".$clang->gT("Description:")."</label>\n"
        . "<textarea cols='70' rows='8' id='description_{$esrow['language']}' name='description_{$esrow['language']}'>{$esrow['description']}</textarea>\n"
        . getEditor("group-desc","description_".$esrow['language'], "[".$clang->gT("Description:", "js")."](".$esrow['language'].")",$surveyid,$gid,'',$action)
        . "\t</li></ul></div>"; 
    }
    $editgroup .= '</div>';
    $editgroup .= "\t<p><input type='submit' class='standardbtn' value='".$clang->gT("Update Group")."' />\n"
    . "\t<input type='hidden' name='action' value='updategroup' />\n"
    . "\t<input type='hidden' name='sid' value=\"{$surveyid}\" />\n"
    . "\t<input type='hidden' name='gid' value='{$gid}' />\n"
    . "\t<input type='hidden' name='language' value=\"{$esrow['language']}\" />\n"
    . "\t</p>\n"
    . "</form>\n";
}

  
?>
