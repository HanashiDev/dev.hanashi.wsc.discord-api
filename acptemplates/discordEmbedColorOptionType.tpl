<div class="discordColorOption">
	<div class="discordColorWrapper">
		<div class="jsDiscordColorPicker{$optionName} discordColorPicker" style="background-color: {$value};" data-color="{$value}" data-store="{$optionName}"></div>
		<input type="hidden" id="{$optionName}" name="values[{$optionName}]" value="{$value}">
	</div>
</div>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/Color/Picker'], function(UiColorPicker) {
		{jsphrase name='wcf.style.colorPicker'}
		{jsphrase name='wcf.style.colorPicker.new'}
		{jsphrase name='wcf.style.colorPicker.current'}
		{jsphrase name='wcf.style.colorPicker.button.apply'}
		{jsphrase name='wcf.style.colorPicker.hue'}
		{jsphrase name='wcf.style.colorPicker.saturation'}
		{jsphrase name='wcf.style.colorPicker.lightness'}
		{jsphrase name='wcf.style.colorPicker.color'}
		{jsphrase name='wcf.style.colorPicker.hexAlpha'}
		{jsphrase name='wcf.style.colorPicker.error.invalidColor'}

		UiColorPicker.fromSelector('.jsDiscordColorPicker{$optionName}');
	});
</script>
