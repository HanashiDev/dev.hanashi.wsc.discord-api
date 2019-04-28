define(['EventHandler', './../../Discord/Gateway', 'Ajax', 'Language', 'Ui/Dialog', 'Dom/Util'], function(EventHandler, DiscordGateway, Ajax, Language, UiDialog, DomUtil) {
    "use strict";

    function DiscordTester() {
        this._connected = false;
        this._template = null;

        this._init();
    }
    DiscordTester.prototype = {
        _init: function() {
            var jsConnectBots = elByClass('jsConnectBot');
            for (var i = 0; i < jsConnectBots.length; i++) {
                jsConnectBots[i].onclick = this._connectBotClick.bind(this);
            }
        },

        _connectBotClick: function(e) {
            var target = e.target.parentNode;
            var objectID = elData(target, 'object-id');

            Ajax.api(this, {
                parameters: {
                    data: {
                        botID: objectID
                    }
                }
            });
        },

        _testBotConnection: function(botToken) {
            // TODO: lang
            this._template = '<div id="BotTestText"><span class="icon icon24 fa-spinner"></span> <span style="margin-left: 10px">Verbindet mit Gateway</span></div>';
            UiDialog.destroy(this);
            UiDialog.open(this);
            
            EventHandler.add('dev.hanashi.wsc.discord.gateway', 'dispatch', this._dispatchData.bind(this));
            var presence = {
                "game": {
                    "name": "Bot Test",
                    "type": 0
                },
                "status": "online",
                "afk": false
            };
            new DiscordGateway(botToken, presence);
            setTimeout(this._connectionError.bind(this), 15 * 1000);
        },

        _dispatchData: function(data) {
            if (data.t == 'READY') {
                this._connected = true;
                var botTestText = elById('BotTestText');
                // TODO: lang
                botTestText.innerHTML = '<div id="BotTestText"><span class="icon icon24 fa-check-circle green"></span> <span style="margin-left: 10px">Erfolgreich verbunden.</span></div>';
            }
        },

        _connectionError: function() {
            if (!this._connected) {
                var botTestText = elById('BotTestText');
                // TODO: lang
                botTestText.innerHTML = '<div id="BotTestText"><span class="icon icon24 fa-times-circle red"></span> <span style="margin-left: 10px">Konnte keine Verbindung zum Gateway herstellen (Timeout).</span></div>';
            }
        },

        _dialogSetup: function() {
            return {
                id: DomUtil.getUniqueId(),
                source: DomUtil.createFragmentFromHtml(this._template),
                options: {
                    title: 'Bot einmalig verbinden' // TODO: lang
                }
            }
        },

        _ajaxSetup: function() {
            return {
				data: {
                    actionName: 'getBotToken',
					className: 'wcf\\data\\discord\\bot\\DiscordBotAction'
				}
			};
        },

        _ajaxSuccess: function(data) {
            if (data.actionName == 'getBotToken') {
                this._testBotConnection(data.returnValues.token);
            }
        }
    };
    return DiscordTester;
});
