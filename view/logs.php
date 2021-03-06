<form method="post" action="" id="logform" name="library" accept-charset="utf-8">
    <input type="hidden" name="tab" value="logs">
	<input type="hidden" id="process" name="process" value="">
    <br>

<ul id="logsFileTab" class="shadetabs">
	<li><a href="#" rel="logs1" class="selected"><?php echo tr('tab_log_file','Serviio log file')?></a></li>
</ul>
<br>

<div style="border:1px solid gray; width:98%; margin-bottom: 1em; padding: 10px">
	<div id="logs1" class="tabcontent">
		<?php echo tr('tab_logs_file_description','The below shows the content of the Serviio generated log file. File location must be set in config.php (incl. filename).')?>
		<br>
		<br>
		<table>
			<tr>
				<td><?php echo tr('tab_logs_file_location','Location of Serviio log file')?>:&nbsp;</td>
				<td><input type="text" id="logfile" name="logfile" size="60" value="<?php echo isset($_COOKIE["logfile"])?$_COOKIE["logfile"]:""?>"></td>
				<td><button type="button" id="addLogFile" name="addLogFile" class="ui-button ui-widget ui-state-default ui-corner-all btn-small" />
						<?php echo tr('button_add_log_file','Choose log file...')?>
					</button>
				</td>
			</tr>
		</table>		
		
		<div align="right">
			<span id="savingMsg" class="savingMsg"></span>
			<input type="submit" id="refresh" name="refresh" value="<?php echo tr('button_refresh','Refresh')?>" onclick=indexes.expandit(7) class="ui-button ui-widget ui-state-default ui-corner-all btn-small" />
			<input type="submit" id="reset" name="reset" value="<?php echo tr('button_reset','Reset')?>" onclick="return confirm('<?php echo tr('status_message_reset','Are you sure you want to reset changes?')?>')" class="ui-button ui-widget ui-state-default ui-corner-all btn-small" />
        <input type="submit" id="submit" name="save" value="<?php echo tr('button_save','Save')?>" class="ui-button ui-widget ui-state-default ui-corner-all btn-small" />
			<br>
		</div>
	</div>
</div>

<ul id="logsContentTab" class="shadetabs">
	<li><a href="#" rel="logs2" class="selected"><?php echo tr('tab_log_content','Log file content')?></a></li>
</ul>

<div id="dialog-form1" title="<?php echo tr('dialog_select_log_file','Select Serviio log file')?>">
    <form accept-charset="utf-8">
        <fieldset>
            <label for="selValue1"><?php echo tr('dialog_select_file','Selected file')?>:&nbsp;</label>
            <input type="text" id="selValue1" name="selValue1" readonly="readonly" size="70" />
            <div id="smallbrowser1"></div>
        </fieldset>
    </form>
</div>

</form>

<div style="border:1px solid gray; width:98%; margin-bottom: 1em; padding: 10px">
	<div id="logs2" class="tabcontent">
		<br>
			<?php
				if (!empty($_COOKIE["logfile"])) {
				$log = $_COOKIE["logfile"];
				$file = fopen( $log, "r") or exit('<strong><span style="color:#FF0000;text-align:left;">'.tr('tab_log_open_error','Unable to open Serviio log file!').'</span></strong>');
				$stack = array();
				//Output a line of the file until the end is reached
				while(!feof($file))
				{
					array_push($stack, fgets($file));
				}
				fclose($file);
				$reversed = array_reverse($stack);
				foreach($reversed as $value) {
					if (strpos($value,'WARN') !== false) {
						echo "<span style='background-color:yellow'>".$value. "</span><br>";
					}
					elseif (strpos($value,'ERROR') !== false) {
						echo "<span style='background-color:red'>".$value. "</span><br>";
					}
					else {
						echo $value. "<br>";
					}
				}
				}
				else {
					echo tr('tab_log_empty','No log file selected.');
				}
			?>
		<br>
	</div>
</div>