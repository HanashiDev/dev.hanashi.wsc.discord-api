<?php

namespace wcf\system\gridView\admin;

use Override;
use wcf\data\DatabaseObject;
use wcf\data\discord\webhook\DiscordWebhook;
use wcf\data\discord\webhook\DiscordWebhookList;
use wcf\system\cache\builder\DiscordGuildChannelsCacheBuilder;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\interaction\admin\DiscordWebhookInteractions;
use wcf\system\interaction\bulk\admin\DiscordWebhookBulkInteractions;
use wcf\system\view\filter\TextFilter;
use wcf\system\view\filter\TimeFilter;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * @extends AbstractGridView<DiscordWebhook, DiscordWebhookList>
 */
final class DiscordWebhookGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('webhookID')
                ->label('wcf.global.objectID')
                ->sortable(),
            GridViewColumn::for('channelID')
                ->label('wcf.acp.discordWebhookList.channelID')
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        #[Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof DiscordWebhook);

                            $channels = DiscordGuildChannelsCacheBuilder::getInstance()->getData();

                            if (!isset($channels[$row->botID][$value])) {
                                return $value;
                            }

                            return \sprintf(
                                '%s<br>(%s)',
                                StringUtil::encodeHTML($channels[$row->botID][$value]['name']),
                                StringUtil::encodeHTML($value)
                            );
                        }
                    },
                ])
                ->sortable(),
            GridViewColumn::for('webhookTitle')
                ->label('wcf.acp.discordWebhookList.webhookTitle')
                ->titleColumn()
                ->sortable()
                ->filter(TextFilter::class),
            GridViewColumn::for('webhookName')
                ->label('wcf.acp.discordWebhookList.webhookName')
                ->sortable()
                ->filter(TextFilter::class),
            GridViewColumn::for('botID')
                ->label('wcf.acp.discordBotList.server')
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        public function render(mixed $value, mixed $context = null): string
                        {
                            \assert($context instanceof DiscordWebhook);

                            $bot = $context->getDiscordBot();

                            $content = '';
                            if (!empty($bot->guildIcon)) {
                                $content = \sprintf(
                                    '<img
                                        src="https://cdn.discordapp.com/icons/%s/%s.png"
                                        style="max-width: 32px; border-radius: 50%%; margin-right: 10px;"
                                     >',
                                    $bot->guildID,
                                    $bot->guildIcon
                                );
                            }

                            return $content . $bot->guildName;
                        }
                    },
                ]),
            GridViewColumn::for('webhookTime')
                ->label('wcf.global.date')
                ->renderer(new TimeColumnRenderer())
                ->sortable()
                ->filter(TimeFilter::class),
        ]);

        $provider = new DiscordWebhookInteractions();
        $this->setInteractionProvider($provider);
        $this->setBulkInteractionProvider(new DiscordWebhookBulkInteractions());

        $this->setDefaultSortField('webhookID');
        $this->setDefaultSortOrder('ASC');
    }

    #[Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.discord.canManageWebhooks');
    }

    #[Override]
    protected function createObjectList(): DiscordWebhookList
    {
        return new DiscordWebhookList();
    }
}
