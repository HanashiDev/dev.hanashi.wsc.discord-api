<?php

namespace wcf\system\gridView\admin;

use Override;
use wcf\acp\form\DiscordBotEditForm;
use wcf\data\DatabaseObject;
use wcf\data\discord\bot\DiscordBot;
use wcf\data\discord\bot\DiscordBotList;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\interaction\admin\DiscordBotInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\view\filter\TextFilter;
use wcf\system\view\filter\TimeFilter;
use wcf\system\WCF;

/**
 * @extends AbstractGridView<DiscordBot, DiscordBotList>
 */
final class DiscordBotGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('botID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('botName')
                ->label('wcf.acp.discordBotList.botName')
                ->sortable()
                ->titleColumn()
                ->filter(TextFilter::class),
            GridViewColumn::for('guildName')
                ->label('wcf.acp.discordBotList.server')
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        #[Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof DiscordBot);

                            $content = '';
                            if (!empty($row->guildIcon)) {
                                $content = \sprintf(
                                    '<img
                                        src="https://cdn.discordapp.com/icons/%s/%s.png"
                                        style="max-width: 32px; border-radius: 50%%; margin-right: 10px;"
                                     >',
                                    $row->guildID,
                                    $row->guildIcon
                                );
                            }

                            return $content . $row->guildName;
                        }
                    },
                ])
                ->sortable()
                ->filter(TextFilter::class),
            GridViewColumn::for('botTime')
                ->label('wcf.global.date')
                ->renderer(new TimeColumnRenderer())
                ->sortable()
                ->filter(TimeFilter::class),
        ]);

        $provider = new DiscordBotInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(DiscordBotEditForm::class),
        ]);
        $this->setInteractionProvider($provider);

        $this->setDefaultSortField('botID');
        $this->setDefaultSortOrder('ASC');
    }

    #[Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.discord.canManageConnection');
    }

    #[Override]
    protected function createObjectList(): DiscordBotList
    {
        return new DiscordBotList();
    }
}
