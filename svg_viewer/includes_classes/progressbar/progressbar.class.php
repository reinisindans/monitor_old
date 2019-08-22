<?php
/*
*	PHP Fortschrittsbalken v1.2
*	(c) 2008 by Fabian Schlieper
*	fabian@fabi.me
*	http://fabi.me/
*/

class progressbar
{
	static $js_prefix = "_pb_";
	
	var $id = 0;
	var $value = 0;
	var $steps = 100;
	var $width = 100;
	var $height = 20;
	var $color = '#0C0';
	var $bgcolor = '#FFF';
	var $inner_styleclass = '';
	var $outer_styleclass = '';
	var $show_digits = true;
	
	function progressbar( $value = 0, $steps = 100, $width = 100, $height = 20, $color = '#0C0', $bgcolor = '#FFF', $inner_styleclass = '', $outer_styleclass = '')
	{
		global $progress_bars;		
		if(!isset($progress_bars))
			$progress_bars = 0;
			
		$this->id = $progress_bars;
		$this->value = $value;
		$this->steps = $steps;
		$this->width = $width;
		$this->height = $height;
		$this->color = $color;
		$this->bgcolor = $bgcolor;
		$this->inner_styleclass = $inner_styleclass;
		$this->outer_styleclass = $outer_styleclass;
		
		$progress_bars++;
	}
	
	function set_show_digits($show = true)
	{
		$this->show_digits = $show;
	}
	
	function print_code()
	{
		if($this->id < 1)
		{
			progressbar::execute_js("

	var ".progressbar::$js_prefix."bars = new Array();
	var ".progressbar::$js_prefix."digits = new Array();
	var ".progressbar::$js_prefix."values = new Array();
	var ".progressbar::$js_prefix."steps = new Array();
	var ".progressbar::$js_prefix."widths = new Array();
	var ".progressbar::$js_prefix."digclchanged = new Array();

	function ".progressbar::$js_prefix."init(id, value, steps, width)
	{
		".progressbar::$js_prefix."bars[id] = document.getElementById('".progressbar::$js_prefix."' + id);
		".progressbar::$js_prefix."digits[id] = document.getElementById('".progressbar::$js_prefix."d' + id);
		".progressbar::$js_prefix."values[id] = value;
		".progressbar::$js_prefix."steps[id] = steps;
		".progressbar::$js_prefix."widths[id] = width;
	}	
	function ".progressbar::$js_prefix."setvalue(id, value)
	{
		".progressbar::$js_prefix."values[id] = value;
		var p = (".progressbar::$js_prefix."values[id] / ".progressbar::$js_prefix."steps[id]);
		
		".progressbar::$js_prefix."digits[id].innerHTML = '' + Math.round( p * 100 ) + ' %';	
		".progressbar::$js_prefix."bars[id].style.width = '' + Math.round( p * ".progressbar::$js_prefix."widths[id] ) + 'px';
		
		if((typeof ".progressbar::$js_prefix."digclchanged[id] == 'undefined' || !".progressbar::$js_prefix."digclchanged[id]) && p >= 0.5) {
			".progressbar::$js_prefix."digits[id].style.color = document.getElementById('".progressbar::$js_prefix."b' + id).style.backgroundColor;
			".progressbar::$js_prefix."digclchanged[id] = true;
		}
	}

	function ".progressbar::$js_prefix."s(id,step)	{ ".progressbar::$js_prefix."setvalue(id, ".progressbar::$js_prefix."values[id] + step); }
	function ".progressbar::$js_prefix."complete(id)	{ ".progressbar::$js_prefix."setvalue(id, ".progressbar::$js_prefix."steps[id]); }
	function ".progressbar::$js_prefix."reset(id)	{ ".progressbar::$js_prefix."setvalue(id, 0); }
", false);
		}
		
		progressbar::_echo('<div id="'.progressbar::$js_prefix.'b'.$this->id.'" style="width:'.$this->width.'px; height:'.$this->height.'px; background-color:' . $this->bgcolor . ';' . ( (!empty($this->outer_styleclass)) ? '" class="'.$this->outer_styleclass.'"' : ' border: 1px solid #000;"') . '>' .
				'<div id="'.progressbar::$js_prefix.'d'.$this->id.'" style="width:'.$this->width.'px; height:'.$this->height.'px; text-align:center; vertical-align:middle; position:absolute; z-index:3; color:' . $this->color . '; display:' . ($this->show_digits ? 'block' : 'none') . '"></div>' .
				'<div id="'.progressbar::$js_prefix.$this->id.'" style="width:0px; height:'.$this->height.'px; background-color:' . $this->color . ';"' . ( (!empty($this->inner_styleclass)) ? ' class="'.$this->inner_styleclass.'"' : '') . '></div></div>' );
		progressbar::execute_js(progressbar::$js_prefix.'init('.$this->id.','.$this->value.','.$this->steps.','.$this->width.');');
	}	

	function step($d=1)
	{
		if($this->value >= $this->steps)
			return;		
		if(($this->value + $d) > $this->steps) $d = $this->steps - $this->value;
		$this->value += $d;
		progressbar::execute_js(progressbar::$js_prefix.'s('.$this->id.','.$d.');');
	}
	
	function reset()
	{
		$this->value = 0;
		progressbar::execute_js(progressbar::$js_prefix.'reset('.$this->id.');');
	}
	
	function complete()
	{
		$this->value = $this->steps;
		progressbar::execute_js(progressbar::$js_prefix.'complete('.$this->id.');');
	}

	private static function execute_js($code, $short_tag=true)
	{
		$code = trim($code);
		if($short_tag)
			progressbar::_echo('<script>'. $code . '</script>');
		else
			progressbar::_echo('<script type="text/javascript"><!--'."\n$code\n".'// --></script>');
	}
	
	private static function _echo( $string )
	{
		echo $string;
		@ob_flush();
		@flush();
	}
	
	private static function invert_color($color){
    $color = str_replace('#', '', $color);
	$len = strlen($color);
    if ($len != 6 && $len != 3){ return '#000000'; }
    $rgb = '';
	$short = ($len == 3);
	for ($x=0;$x<3;$x++){
		if(!$short)
			$c = 255 - hexdec(substr($color,(2*$x),2));
		else
			$c = 255 - hexdec(substr($color,($x),1) . substr($color,($x),1));
		$c = ($c < 0) ? 0 : dechex($c);
		$rgb .= (strlen($c) < 2) ? '0'.$c : $c;
	}

    return '#'.$rgb;
}


}
?>