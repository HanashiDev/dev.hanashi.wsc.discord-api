{if $bots|count > 1}
    <div class="section tabMenuContainer" data-active="{$optionName}" data-store="activeTabMenuItem">
        <div id="{$optionName}" class="tabMenuContainer tabMenuContent">
            <nav class="menu">
                <ul>
                    {foreach from=$bots item=$bot}
                        <li>
                            <a href="#{$optionName}-{$bot['botID']}">{$bot['botName']}</a>
                        </li>
                    {/foreach}
                </ul>
            </nav>
            {foreach from=$bots item=$bot}
                <div id="{$optionName}-{$bot['botID']}" class="tabMenuContent hidden" data-name="{$optionName}-{$bot['botID']}">
                    <div class="section">
                        <ul class="scrollableCheckboxList" id="{$optionName}_{$bot['botID']}" style="height: 200px;">
                            {foreach from=$bot['roles'] item=role}
                                <li>
                                    <label><input type="checkbox" name="values[{$optionName}][{$bot['botID']}][]" value="{$role['id']}"{if !$value[$bot['botID']]|empty && $value[$bot['botID']]|is_array && $role['id']|in_array:$value[$bot['botID']]} checked{/if}> {$role['name']}</label>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                </div>
                <script data-relocate="true">
                    require(['WoltLabSuite/Core/Ui/ItemList/Filter'], function(UiItemListFilter) {
                        new UiItemListFilter('{$optionName|encodeJS}_{$bot['botID']|encodeJS}');
                    });
                </script>
            {/foreach}
        </div>
    </div>
    <script data-relocate="true">
        require(['Language'], function(Language) {
            Language.addObject({
                'wcf.global.filter.button.visibility': '{jslang}wcf.global.filter.button.visibility{/jslang}',
                'wcf.global.filter.button.clear': '{jslang}wcf.global.filter.button.clear{/jslang}',
                'wcf.global.filter.error.noMatches': '{jslang}wcf.global.filter.error.noMatches{/jslang}',
                'wcf.global.filter.placeholder': '{jslang}wcf.global.filter.placeholder{/jslang}',
                'wcf.global.filter.visibility.activeOnly': '{jslang}wcf.global.filter.visibility.activeOnly{/jslang}',
                'wcf.global.filter.visibility.highlightActive': '{jslang}wcf.global.filter.visibility.highlightActive{/jslang}',
                'wcf.global.filter.visibility.showAll': '{jslang}wcf.global.filter.visibility.showAll{/jslang}'
            });
        });
    </script>
{else if $bots|count == 1}
    <ul class="scrollableCheckboxList" id="{$optionName}" style="height: 200px;">
        {foreach from=$bots[0]['roles'] item=role}
            <li>
                <label><input type="checkbox" name="values[{$optionName}][{$bots[0]['botID']}][]" value="{$role['id']}"{if !$value[$bots[0]['botID']]|empty && $value[$bots[0]['botID']]|is_array && $role['id']|in_array:$value[$bots[0]['botID']]} checked{/if}> {$role['name']}</label>
            </li>
        {/foreach}
    </ul>

    <script data-relocate="true">
        require(['Language', 'WoltLabSuite/Core/Ui/ItemList/Filter'], function(Language, UiItemListFilter) {
            Language.addObject({
                'wcf.global.filter.button.visibility': '{jslang}wcf.global.filter.button.visibility{/jslang}',
                'wcf.global.filter.button.clear': '{jslang}wcf.global.filter.button.clear{/jslang}',
                'wcf.global.filter.error.noMatches': '{jslang}wcf.global.filter.error.noMatches{/jslang}',
                'wcf.global.filter.placeholder': '{jslang}wcf.global.filter.placeholder{/jslang}',
                'wcf.global.filter.visibility.activeOnly': '{jslang}wcf.global.filter.visibility.activeOnly{/jslang}',
                'wcf.global.filter.visibility.highlightActive': '{jslang}wcf.global.filter.visibility.highlightActive{/jslang}',
                'wcf.global.filter.visibility.showAll': '{jslang}wcf.global.filter.visibility.showAll{/jslang}'
            });
            
            new UiItemListFilter('{$optionName|encodeJS}');
        });
    </script>
{else}
    <p class="info">{lang}wcf.acp.discordBotSelectOptionType.noBot{/lang}</p>
{/if}
