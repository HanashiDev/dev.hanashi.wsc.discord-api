define(['EventHandler'], function(EventHandler) {
    "use strict";

    function DiscordGateway(token, presence = {}) {
        this._presence = {}
        this._token = null;
        this._socket = null;
        this._heartbeatInterval = 0;
        this._seq = null;

        this._init(token, presence);
    }
    DiscordGateway.prototype = {
        _init: function(token, presence = {}) {
            this._presence = presence;
            this._token = token;
            this._socket = new WebSocket('wss://gateway.discord.gg/?v=6&encoding=json');

            this._socket.onopen = this._onopen.bind(this);
            this._socket.onerror = this._onerror.bind(this);
            this._socket.onmessage = this._onmessage.bind(this);

            var data = {
                token: this._token,
                socket: this._socket
            };
            EventHandler.fire('dev.hanashi.wsc.discord.gateway', 'init', data);
        },

        _onopen: function() {
            EventHandler.fire('dev.hanashi.wsc.discord.gateway', 'onopen');
        },

        _onerror: function(error) {
            EventHandler.fire('dev.hanashi.wsc.discord.gateway', 'onerror', error);
        },

        _onmessage: function(e) {
            var data = JSON.parse(e.data);
            if (data.op == 0) {
                this._seq = data.s;
                EventHandler.fire('dev.hanashi.wsc.discord.gateway', 'dispatch', data);
            } else if (data.op == 10) {
                // heartbeating
                this._heartbeatInterval = data.d.heartbeat_interval;
                this._sendHeartBeat();

                EventHandler.fire('dev.hanashi.wsc.discord.gateway', 'beforeVerify', data);

                // verify
                this._verify();

                EventHandler.fire('dev.hanashi.wsc.discord.gateway', 'afterVerify', data);
            } else if (data.op == 11) {
                this._sendHeartBeat();
            } else {
                EventHandler.fire('dev.hanashi.wsc.discord.gateway', 'unknownReceived', data);
            }
        },

        _sendHeartBeat: function() {
            setTimeout(this._heartbeat.bind(this), this._heartbeatInterval);
        },

        _heartbeat: function() {
            var sendData = {
                op: 1,
                d: this._seq
            };
            this._socket.send(JSON.stringify(sendData));
        },

        _verify: function() {
            var sendData = {
                op: 2,
                d: {
                    token: this._token,
                    properties: {},
                    presence: this._presence
                }
            };
            this._socket.send(JSON.stringify(sendData));
        }
    }

    return DiscordGateway;
});
