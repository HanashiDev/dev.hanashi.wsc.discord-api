import * as EventHandler from "WoltLabSuite/Core/Event/Handler";

export class DiscordGateway {
    private readonly token: string;
    private readonly presence: object = {};

    protected socket: WebSocket;

    private heartbeatInterval: number = 0;
    private seq: number | null = null;

    constructor(token: string, presence: object = {}) {
        this.token = token;
        this.presence = presence;

        this.socket = new WebSocket('wss://gateway.discord.gg/?v=6&encoding=json');
        this.socket.addEventListener('open', () => this.onopen());
        this.socket.addEventListener('error', (ev: Event) => this.onerror(ev));
        this.socket.addEventListener('message', (ev: MessageEvent<any>) => this.onmessage(ev));

        const data = {
            token: this.token,
            socket: this.socket
        };
        EventHandler.fire('dev.hanashi.wsc.discord.gateway', 'init', data);
    }

    protected onopen() {
        EventHandler.fire('dev.hanashi.wsc.discord.gateway', 'onopen');
    }

    protected onerror(ev: Event) {
        EventHandler.fire('dev.hanashi.wsc.discord.gateway', 'onerror', ev);
    }

    protected onmessage(ev: MessageEvent<any>) {
        const data = JSON.parse(ev.data);

        switch (data.op) {
            case 0: {
                this.seq = data.s;
                EventHandler.fire('dev.hanashi.wsc.discord.gateway', 'dispatch', data);
                break;
            }
            case 10: {
                // heartbeating
                this.heartbeatInterval = data.d.heartbeat_interval;
                this.sendHeartbeat();

                EventHandler.fire('dev.hanashi.wsc.discord.gateway', 'beforeVerify', data);

                // verify
                this.verify();

                EventHandler.fire('dev.hanashi.wsc.discord.gateway', 'afterVerify', data);
                break;
            }
            case 11: {
                this.sendHeartbeat();
                break;
            }
            default: {
                EventHandler.fire('dev.hanashi.wsc.discord.gateway', 'unknownReceived', data);
            }
        }
    }

    private sendHeartbeat() {
        setTimeout(() => this.heartbeat(), this.heartbeatInterval);
    }

    private heartbeat() {
        const sendData = {
            op: 1,
            d: this.seq
        };
        this.socket.send(JSON.stringify(sendData));
    }

    private verify() {
        var sendData = {
            op: 2,
            d: {
                token: this.token,
                properties: {},
                presence: this.presence
            }
        };
        this.socket.send(JSON.stringify(sendData));
    }
}

export default DiscordGateway;
