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
                        <select id="{$optionName}" name="values[{$optionName}][{$bot['botID']}][]" multiple size="10">
                            {foreach from=$bot['channels'] item=channel}
                                {if $channel['type'] == 4}
                                    <optgroup label="{$channel['name']}">
                                        {foreach from=$channel['childs'] item=$childChannel}
                                            {if $childChannel['type'] == 0}
                                                <option value="{$childChannel['id']}"{if !$value[$bot['botID']]|empty && $value[$bot['botID']]|is_array && $childChannel['id']|in_array:$value[$bot['botID']]} selected{/if}>{$childChannel['name']}</option>
                                            {/if}
                                        {/foreach}
                                    </optgroup>
                                {else if $channel['type'] == 0}
                                    <option value="{$channel['id']}"{if !$value[$bot['botID']]|empty && $value[$bot['botID']]|is_array && $channel['id']|in_array:$value[$bot['botID']]} selected{/if}>{$channel['name']}</option>
                                {/if}
                            {/foreach}
                        </select>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
{else if $bots|count == 1}
    <select id="{$optionName}" name="values[{$optionName}][{$bots[0]['botID']}][]" multiple size="10">
        {foreach from=$bots[0]['channels'] item=channel}
            {if $channel['type'] == 4}
                <optgroup label="{$channel['name']}">
                    {foreach from=$channel['childs'] item=$childChannel}
                        {if $childChannel['type'] == 0}
                            <option value="{$childChannel['id']}"{if !$value[$bots[0]['botID']]|empty && $value[$bots[0]['botID']]|is_array && $childChannel['id']|in_array:$value[$bots[0]['botID']]} selected{/if}>{$childChannel['name']}</option>
                        {/if}
                    {/foreach}
                </optgroup>
            {else if $channel['type'] == 0}
                <option value="{$channel['id']}"{if !$value[$bots[0]['botID']]|empty && $value[$bots[0]['botID']]|is_array && $channel['id']|in_array:$value[$bots[0]['botID']]} selected{/if}>{$channel['name']}</option>
            {/if}
        {/foreach}
    </select>
{else}
    <p class="info">{lang}wcf.acp.discordBotSelectOptionType.noBot{/lang}</p>
{/if}
