<?php
/*
Plugin Name: BBcode
Plugin URI: http://www.bbcode.templarian.com/
Description: <a href="../wp-admin/options-general.php?page=bbcode.php">Admin Panel</a> for BBcode allows edit and delete of tags. Tags are saved into an XML file in the plugin directory.
Author: Templarian
Version: 1.0.1
Author URI: http://www.templarian.com/
*/

//XML CLASS AND PARSE FUNCTION//
class XMLParser
{
    var $parser;
    var $filePath;
    var $document;
    var $currTag;
    var $tagStack;
   
    function XMLParser($path)
    {
    $this->parser = xml_parser_create();
    $this->filePath = $path;
    $this->document = array();
    $this->currTag =& $this->document;
    $this->tagStack = array();
    }
   
    function parse()
    {
        xml_set_object($this->parser, $this);
        xml_set_character_data_handler($this->parser, 'dataHandler');
        xml_set_element_handler($this->parser, 'startHandler', 'endHandler');
    if(!($fp = fopen($this->filePath, "r")))
        {
            die("Cannot open XML data file: $this->filePath");
            return false;
        }
   
        while($data = fread($fp, 4096))
        {
            if(!xml_parse($this->parser, $data, feof($fp)))
            {
                die(sprintf("XML error: %s at line %d",
                            xml_error_string(xml_get_error_code($this->parser)),
                            xml_get_current_line_number($this->parser)));
            }
        }
   
        fclose($fp);
    xml_parser_free($this->parser);
   
        return true;
    }
   
    function startHandler($parser, $name, $attribs)
    {
        if(!isset($this->currTag[$name]))
            $this->currTag[$name] = array();
       
        $newTag = array();
        if(!empty($attribs))
            $newTag['ATTR'] = $attribs;
        array_push($this->currTag[$name], $newTag);
       
        $t =& $this->currTag[$name];
        $this->currTag =& $t[count($t)-1];
        array_push($this->tagStack, $name);
    }
   
    function dataHandler($parser, $data)
    {
        $data = trim($data);
       
        if(!empty($data))
        {
            if(isset($this->currTag['DATA']))
                $this->currTag['DATA'] .= $data;
            else
                $this->currTag['DATA'] = $data;
        }
    }
   
    function endHandler($parser, $name)
    {
        $this->currTag =& $this->document;
        array_pop($this->tagStack);
       
        for($i = 0; $i < count($this->tagStack); $i++)
        {
            $t =& $this->currTag[$this->tagStack[$i]];
            $this->currTag =& $t[count($t)-1];
        }
    }
}

function filter_bbcode($content) {
	$i = 0;
	$folder = WP_CONTENT_DIR;
	$p = new XMLParser($folder."/plugins/bbcode-w-editor/bbcode.xml");
	$p->parse();
	foreach ($p->document['BBCODE'][0]['BB'] as $key => $value){
		if($p->document['BBCODE'][0]['BB'][$key]['ATTR']['ENABLED'] == 'true'){
			$ta[$i] = html_entity_decode(symboltospace($p->document['BBCODE'][0]['BB'][$key]['INPUT'][0]['DATA']));
			$i++;
			$ta[$i] = html_entity_decode(symboltospace($p->document['BBCODE'][0]['BB'][$key]['OUTPUT'][0]['DATA']));
			$i++;
		}
	}
	$j = 0;
	for($i=0;$i<count($ta);$i++){
		if($i/2 == round($i/2)){
			//input//
			$ta[$i] = preg_replace('/\[/','\\\[',$ta[$i]);
			$ta[$i] = preg_replace('/\]/','\\\]',$ta[$i]);
			$ta[$i] = preg_replace('/<>/','(.*?)',$ta[$i]);
			$ta[$i] = preg_replace('/<1>/','(.*?)',$ta[$i]);
			$ta[$i] = preg_replace('/<2>/','(.*?)',$ta[$i]);
			$ta[$i] = preg_replace('/<3>/','(.*?)',$ta[$i]);
			$ta[$i] = preg_replace('/<4>/','(.*?)',$ta[$i]);
			$ta[$i] = preg_replace('/<5>/','(.*?)',$ta[$i]);
			$ta[$i] = preg_replace('/<6>/','(.*?)',$ta[$i]);
			$ta[$i] = preg_replace('/<7>/','(.*?)',$ta[$i]);
			$ta[$i] = preg_replace('/<8>/','(.*?)',$ta[$i]);
			$ta[$i] = preg_replace('/<9>/','(.*?)',$ta[$i]);
			$find[$j] = "'".$ta[$i]."'is";
		}else{
			//output//
			$ta[$i] = preg_replace('/\[-\]/',"\r\n",$ta[$i]);
			$ta[$i] = preg_replace('/\[1\]/','\\\\1',$ta[$i]);
			$ta[$i] = preg_replace('/\[2\]/','\\\\2',$ta[$i]);
			$ta[$i] = preg_replace('/\[3\]/','\\\\3',$ta[$i]);
			$ta[$i] = preg_replace('/\[4\]/','\\\\4',$ta[$i]);
			$ta[$i] = preg_replace('/\[5\]/','\\\\5',$ta[$i]);
			$ta[$i] = preg_replace('/\[6\]/','\\\\6',$ta[$i]);
			$ta[$i] = preg_replace('/\[7\]/','\\\\7',$ta[$i]);
			$ta[$i] = preg_replace('/\[8\]/','\\\\8',$ta[$i]);
			$ta[$i] = preg_replace('/\[9\]/','\\\\9',$ta[$i]);
			$replace[$j] = $ta[$i];
			$j++;
		}
	}
        $content = preg_replace($find,$replace,$content);
	return definedBBcode($content);
}
function definedBBcode($str){
	return str_replace('[~URL]', get_bloginfo('wpurl'), $str);
}
function spacetosymbol($str){
	return str_replace("\r\n", '[~]', str_replace(' ', '[-]', $str));
}
function symboltospace($str){
	return str_replace('[~]', "\r\n", str_replace('[-]', ' ', $str));
}

//START PLUGIN//
if (!class_exists("BBcode")) {
	class BBcode {
		var $adminOptionsName = "BBcodeAdminOptions";
		function BBcode() { //constructor
			
		}
		
		function printAdminPage() {
					?>
<div class=wrap>
<form method="post" name="bbcode_form" id="bbcode_form" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<h2>BBcode Plugin</h2>
<?php
					if ($_POST['bbcode_id'] != "" and $_POST['bbcode_window'] == 'edit') {
						//GET CURRENT BBcode//
							$folder = WP_CONTENT_DIR;
							$p = new XMLParser($folder."/plugins/bbcode-w-editor/bbcode.xml");
							$p->parse();
							$key = $_POST['bbcode_id'];
							$bbcode_enabled = $p->document['BBCODE'][0]['BB'][$key]['ATTR']['ENABLED'];
							$bbcode_name = html_entity_decode(symboltospace($p->document['BBCODE'][0]['BB'][$key]['NAME'][0]['DATA']));
							$bbcode_description = html_entity_decode(symboltospace($p->document['BBCODE'][0]['BB'][$key]['DESCIPTION'][0]['DATA']));
							$bbcode_tag = html_entity_decode(symboltospace($p->document['BBCODE'][0]['BB'][$key]['INPUT'][0]['DATA']));
							$bbcode_html = html_entity_decode(symboltospace($p->document['BBCODE'][0]['BB'][$key]['OUTPUT'][0]['DATA']));
?>
					<div class="submit">
						<input type="submit" name="bbcode_addsubmit" value="<?php _e('Update Tag &raquo;', 'BBcode') ?>" />
					</div>
					
					<h3>Name:</h3>
					
					<p><input style="width:100%;" type="text" name="bbcode_name" value="<?php echo(htmlentities($bbcode_name)); ?>" /><br />Shown on front BBcode admin panel</p>
					
					<h3>Description:</h3>
					
					<p><textarea style="width:100%;" name="bbcode_description"><?php echo(htmlentities($bbcode_description)); ?></textarea><br />Shown on front BBcode admin panel</p>
					
					<h3>Tag:</h3>
					
					<p><input style="width:100%;" type="text" name="bbcode_tag" value="<?php echo(htmlentities($bbcode_tag)); ?>" /><br />Ex: [link=<1>]<2>[/link]</p>
					
					<h3>HTML:</h3>
					
					<p><textarea style="width:100%;" rows="6" name="bbcode_html"><?php echo(htmlentities($bbcode_html)); ?></textarea><br />Ex: &lt;a href="[1]"&gt;[2]&lt;a&gt;</p>
					
					<h3>Warning:</h3>
					
					<p>Do not use <strong>[-]</strong> and <strong>[~]</strong> as they will be removed.</p>
					
					<input type="hidden" name="bbcode_enabled" value="<?php echo($bbcode_enabled); ?>" />
					<input type="hidden" name="bbcode_updateid" value="<?php echo($key); ?>" />
					
					<div class="submit">
						<input type="submit" name="bbcode_addsubmit" value="<?php _e('Update Tag &raquo;', 'BBcode') ?>" />
					</div>
<?php
					
					}elseif (isset($_POST['bbcode_add'])) {
?>
					<div class="submit">
						<input type="submit" name="bbcode_addsubmit" value="<?php _e('Add Tag &raquo;', 'BBcode') ?>" />
					</div>
					
					<h3>Name:</h3>
					
					<p><input style="width:100%;" type="text" name="bbcode_name" value="" /><br />Shown on front BBcode admin panel</p>
					
					<h3>Description:</h3>
					
					<p><textarea style="width:100%;" name="bbcode_description"></textarea><br />Shown on front BBcode admin panel</p>
					
					<h3>Tag:</h3>
					
					<p><input style="width:100%;" type="text" name="bbcode_tag" value="" /><br />Ex: [link=<1>]<2>[/link]</p>
					
					<h3>HTML:</h3>
					
					<p><textarea style="width:100%;" rows="6" name="bbcode_html"></textarea><br />Ex: &lt;a href="[1]"&gt;[2]&lt;a&gt;</p>
					
					<h3>Warning:</h3>
					
					<p>Do not use <strong>[-]</strong> and <strong>[~]</strong> as they will be removed.</p>
					
					<div class="submit">
						<input type="submit" name="bbcode_addsubmit" value="<?php _e('Add Tag &raquo;', 'BBcode') ?>" />
					</div>
<?php
					}else{
						if (isset($_POST['bbcode_addsubmit']) or ($_POST['bbcode_id'] != "" and ($_POST['bbcode_window'] == 'delete' or $_POST['bbcode_window'] == 'enable' or $_POST['bbcode_window'] == 'disable'))) {
							//GET CURRENT BBcode//
							$folder = WP_CONTENT_DIR;
							$p = new XMLParser($folder."/plugins/bbcode-w-editor/bbcode.xml");
							$p->parse();
							$bbcode_enabled = array();
							$bbcode_name = array();
							$bbcode_description = array();
							$bbcode_tag = array();
							$bbcode_html = array();
							$i = 0;
							foreach ($p->document['BBCODE'][0]['BB'] as $key => $value){
								$bbcode_enabled[$i] = $p->document['BBCODE'][0]['BB'][$key]['ATTR']['ENABLED'];
								$bbcode_name[$i] = html_entity_decode($p->document['BBCODE'][0]['BB'][$key]['NAME'][0]['DATA']);
								$bbcode_description[$i] = html_entity_decode($p->document['BBCODE'][0]['BB'][$key]['DESCIPTION'][0]['DATA']);
								$bbcode_tag[$i] = html_entity_decode($p->document['BBCODE'][0]['BB'][$key]['INPUT'][0]['DATA']);
								$bbcode_html[$i] = html_entity_decode($p->document['BBCODE'][0]['BB'][$key]['OUTPUT'][0]['DATA']);
								$i++;
							}
							//ENABLE/DISABLE/DELETE//
							$bbcode_enable = true;
							$bbcode_delete = false;
							if ($_POST['bbcode_id'] != "" and $_POST['bbcode_window'] == 'delete') {
								$bbcode_delete = true;
							}elseif ($_POST['bbcode_id'] != "" and $_POST['bbcode_window'] == 'enable') {
								$bbcode_enable = true;
							}elseif ($_POST['bbcode_id'] != "" and $_POST['bbcode_window'] == 'disable') {
								$bbcode_enable = false;
							}
							//GET POST BBcode UPDATE/ADD//
							if(!$bbcode_delete){
								if(isset($_POST['bbcode_updateid'])){
									$bbcode_uid = $_POST['bbcode_updateid'];
									$bbcode_enabled[$bbcode_uid] = $_POST['bbcode_enabled'];
									$bbcode_name[$bbcode_uid] = $_POST['bbcode_name'];
									$bbcode_description[$bbcode_uid] = $_POST['bbcode_description'];
									$bbcode_tag[$bbcode_uid] = $_POST['bbcode_tag'];
									$bbcode_html[$bbcode_uid] = $_POST['bbcode_html'];
								}elseif($_POST['bbcode_window'] == 'enable' or $_POST['bbcode_window'] == 'disable'){
									$bbcode_uid = $_POST['bbcode_id'];
									$bbcode_enabled[$bbcode_uid] = $_POST['bbcode_window'] == 'enable' ? 'true' : 'false';
								}else{
									$bbcode_enabled[$i] = 'true';
									$bbcode_name[$i] = $_POST['bbcode_name'];
									$bbcode_description[$i] = $_POST['bbcode_description'];
									$bbcode_tag[$i] = $_POST['bbcode_tag'];
									$bbcode_html[$i] = $_POST['bbcode_html'];
								}
							}
							//WRITE BBcode//
							$x = '';
							$x .= '<?xml version="1.0" encoding="utf-8"?>'."\r\n";
							$x .= '<bbcode>'."\r\n";
							foreach($bbcode_enabled as $key => $value){
								if (!($bbcode_delete and $key == $_POST['bbcode_id'])){
									$x .= '	<bb enabled="'.$bbcode_enabled[$key].'">'."\r\n";
									$x .= '		<name>'.htmlentities(stripslashes(spacetosymbol($bbcode_name[$key]))).'</name>'."\r\n";
									$x .= '		<desciption>'.htmlentities(stripslashes(spacetosymbol($bbcode_description[$key]))).'</desciption>'."\r\n";
									$x .= '		<input>'.htmlentities(stripslashes(spacetosymbol($bbcode_tag[$key]))).'</input>'."\r\n";
									$x .= '		<output>'.htmlentities(stripslashes(spacetosymbol($bbcode_html[$key]))).'</output>'."\r\n";
									$x .= '	</bb>'."\r\n";
								}
							}
							$x .= '</bbcode>';
							$myFile = WP_CONTENT_DIR."/plugins/bbcode-w-editor/bbcode.xml";
							$fh = fopen($myFile, 'w') or die("can't open file");
							fwrite($fh, $x);
							fclose($fh);
							if(isset($_POST['bbcode_updateid'])){?>
							<div class="updated"><p><strong><?php _e("Tag Updated.", "BBcode"); ?></strong></p></div>
							
<?php						}elseif($_POST['bbcode_window'] == 'enable'){?>
							<div class="updated"><p><strong><?php _e("Tag Enabled.", "BBcode"); ?></strong></p></div>
							
<?php						}elseif($_POST['bbcode_window'] == 'disable'){?>
							<div class="updated"><p><strong><?php _e("Tag Disabled.", "BBcode"); ?></strong></p></div>

<?php						}elseif($_POST['bbcode_window'] == 'delete'){?>
							<div class="updated"><p><strong><?php _e("Tag Deleted.", "BBcode"); ?></strong></p></div>
							
<?php						}else{?>
							<div class="updated"><p><strong><?php _e("Tag Added.", "BBcode"); ?></strong></p></div>
<?php						}

						}
?>
<script type="text/javascript">
function bbcode_enable(id, val, msg){
	var answer = confirm(msg+" Tag?");
	if (answer){
		document.bbcode_form.bbcode_id.value = id;
		document.bbcode_form.bbcode_window.value = val;
		document.bbcode_form.submit();
	}
}
function bbcode_edit(id){
	document.bbcode_form.bbcode_id.value = id;
	document.bbcode_form.bbcode_window.value = 'edit';
	document.bbcode_form.submit();
}
function bbcode_delete(id){
	var answer = confirm("Delete Tag");
	if (answer){
		document.bbcode_form.bbcode_id.value = id;
		document.bbcode_form.bbcode_window.value = 'delete';
		document.bbcode_form.submit();
	}
}
</script>
<table width="100%" border="0" cellspacing="0" style="border:1px solid #83B4D8;margin-top:20px;margin-bottom:20px;">
	<tr>
        <th style="background:#E5F3FF;color:#333;text-align:left;padding:2px;" width="100">Name</th>
        <th style="background:#E5F3FF;color:#333;text-align:left;padding:2px;">Description</th>
        <th style="background:#E5F3FF;color:#333;text-align:left;padding:2px;" width="20"></th>
        <th style="background:#E5F3FF;color:#333;text-align:left;padding:2px;" width="20"></th>
        <th style="background:#E5F3FF;color:#333;text-align:left;padding:2px;" width="20"></th>
	</tr>
<?php
$folder = WP_CONTENT_DIR;
$p = new XMLParser($folder."/plugins/bbcode-w-editor/bbcode.xml");
$p->parse();
foreach ($p->document['BBCODE'][0]['BB'] as $key => $value){
	$name = symboltospace($p->document['BBCODE'][0]['BB'][$key]['NAME'][0]['DATA']);
	$description = symboltospace($p->document['BBCODE'][0]['BB'][$key]['DESCIPTION'][0]['DATA']);
	$enabled = $p->document['BBCODE'][0]['BB'][$key]['ATTR']['ENABLED'] == 'true' ? 'Enabled' : 'Disabled';
	$enabled_title = $p->document['BBCODE'][0]['BB'][$key]['ATTR']['ENABLED'] == 'true' ? 'Enable' : 'Disable';
	$enabled_link = $p->document['BBCODE'][0]['BB'][$key]['ATTR']['ENABLED'] == 'true' ? 'disable' : 'enable';
	$enabled_msg = $p->document['BBCODE'][0]['BB'][$key]['ATTR']['ENABLED'] == 'true' ? 'Disable' : 'Enable';
	echo("	<tr style=\"background:#f1f1f1;\">");
	echo("		<td style=\"border-top:1px solid #83B4D8;padding:2px;\">{$name}</td>");
	echo("		<td style=\"border-top:1px solid #83B4D8;padding:2px;\">{$description}</td>");
	echo("		<td style=\"border-top:1px solid #83B4D8;padding:2px;\">&nbsp;<a style=\"border:none;\" title=\"Click to {$enabled_msg}\" href=\"javascript:bbcode_enable('{$key}', '{$enabled_link}', '{$enabled_msg}');\">{$enabled}</a>&nbsp;</td>");
	echo("		<td style=\"border-top:1px solid #83B4D8;padding:2px;\">&nbsp;<a style=\"border:none;\" title=\"Click to edit\" href=\"javascript:bbcode_edit('{$key}');\">Edit</a>&nbsp;</td>");
	echo("		<td style=\"border-top:1px solid #83B4D8;padding:2px;\">&nbsp;<a style=\"border:none;\" title=\"Click to delete\" href=\"javascript:bbcode_delete('{$key}');\">Delete</a>&nbsp;</td>");
	echo("	</tr>");
}
?>
	<tr>
		<td colspan="5" style="border-top:1px solid #83B4D8;background:#E5F3FF;color:#333;text-align:center;">
		<input style="width:100%;border:0;background:#E5F3FF;" type="submit" name="bbcode_add" value="<?php _e('Add Tag', 'BBcode') ?>" />
		</th>
	</tr>
</table>
<input type="hidden" name="bbcode_id" id="bbcode_id" value="" />
<input type="hidden" name="bbcode_window" id="bbcode_window" value="" />
</form>
 </div>
					<?php
					}//End Post isset
			}//End function printAdminPage()
	
	}

} //End Class BBcode

if (class_exists("BBcode")) {
	$dl_pluginSeries = new BBcode();
}

//Initialize the admin panel
if (!function_exists("BBcode_ap")) {
	function BBcode_ap() {
		global $dl_pluginSeries;
		if (!isset($dl_pluginSeries)) {
			return;
		}
		if (function_exists('add_options_page')) {
			add_options_page('BBcode', 'BBcode', 9, basename(__FILE__), array(&$dl_pluginSeries, 'printAdminPage'));
		}
	}	
}

//Actions and Filters	
if (isset($dl_pluginSeries)) {
	//Actions
	add_action('admin_menu', 'BBcode_ap');
	//Filters
	add_filter('the_content','filter_bbcode');
}

?>
